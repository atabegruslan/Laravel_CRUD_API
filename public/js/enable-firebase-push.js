firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

registerServiceWorker();

function registerServiceWorker()
{ // https://stackoverflow.com/questions/41659970/firebase-change-the-location-of-the-service-worker
    if ('serviceWorker' in navigator && 'PushManager' in window) 
    {
        navigator.serviceWorker.getRegistration()
            .then(function(registration) {

                if (registration)
                {
					messaging.useServiceWorker(registration);
					getPermission();
                }
                else
                {
                    navigator.serviceWorker.register(baseUrl + 'js/firebase-service-worker.js')
                        .then(function(newRegistration) {

							messaging.useServiceWorker(newRegistration);
							getPermission();
                        })
                        .catch(function(error) {
                            console.error({error});
                        });
                }
            });
    } 
    else 
    {
        console.warn('Service Worker and Push Messaging are not supported');
    }
}

function getPermission()
{
	messaging.requestPermission()
		.then(function() {
			console.log('Notification permission granted');

			if (isTokenSentToServer())
			{
				console.log('Token already saved');
			}
			else
			{
				getRegToken();
			}
		})
		.catch(function(err) {
			console.error(err);
		});
}

function getRegToken()
{
	messaging.getToken().then((currentToken) => {
		if (currentToken) 
		{
			saveToken(currentToken);
			console.log({currentToken});
		} 
		else 
		{
			console.log('No Instance ID token available. Request permission to generate one.');
			setTokenSentToServer(false);
		}
	}).catch((err) => {
		console.log('An error occurred while retrieving token. ', err);
		setTokenSentToServer(false);
	});
}

function setTokenSentToServer(sent) 
{
	window.localStorage.setItem('sentToServer', sent ? '1' : '0');
}

function isTokenSentToServer() 
{
	return window.localStorage.getItem('sentToServer') === '1';
}

function saveToken(fcmToken) 
{
	const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');

    fetch(baseUrl + 'notification/firebase', {
        method : 'POST',
        body   : JSON.stringify({'token':fcmToken}),
        headers: {
            'Accept'       : 'application/json',
            'Content-Type' : 'application/json',
            'X-CSRF-Token' : token,
            'Authorization': 'Bearer ' + window.token,
        }
    })
    .then((res) => {
        return res.json();
    })
    .then((res) => {
        console.log(res);
        setTokenSentToServer(true);
    })
    .catch((error) => {
        console.error(error)
    });
}

messaging.onMessage(function(payload) {
    
// POST https://fcm.googleapis.com/fcm/send

// 'Authorization: key={LEGACY_API_KEY}'
// 'Content-Type: application/json'

// {
//     "to": "TOKEN",
//     "data" : {
//         "notification" : {
//             "title": "Xxx",
//             "body" : "xxx xxx",
//             "data": {
//                 "entry_url": "https://www.bla.com/",
//                 "base_url": "https://www.whatever.com/"
//             },
//             "actions": [
//                 {
//                     "title": "View",
//                     "action": "view"
//                 },
//                 {
//                     "title": "Close",
//                     "action": "close"
//                 }
//             ],
//             "icon"   : "http://xxx.jpg",
//             "image"  : "http://xxx.jpg",
//             "dir"    : "ltr",
//             "lang"   : "en-US",
//             "tag"    : "one",
//             "vibrate": [100, 50, 100]
//         }
//     }
// }

    var msg = JSON.parse(payload.data.notification);

	var options = {
        body   : msg.body,
        data   : msg.data,
		icon   : msg.icon,
		dir    : msg.dir,
		image  : msg.image,
		lang   : msg.lang,
		tag    : msg.tag,
		//actions: $.parseJSON(msg.actions), // Don't use actions here. Otherwise you will get: TypeError: Failed to construct 'Notification': Actions are only supported for persistent notifications shown using ServiceWorkerRegistration.showNotification().
		vibrate: msg.vibrate
	};

    var notification = new Notification(msg.title, options);

	notification.onclick = function(event) {

        event.preventDefault();
        window.open(event.target.data.base_url, '_blank');
        event.target.close();
	};

	notification.onerror = function(event) {
        console.error(event);
	};
});
