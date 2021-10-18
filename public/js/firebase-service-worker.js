importScripts('https://www.gstatic.com/firebasejs/7.8.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.8.0/firebase-messaging.js');
importScripts('firebase-config.js');

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload) {
	
    var msg = JSON.parse(payload.data.notification);

	const notificationTitle   = msg.title;
	const notificationOptions = {
		body   : msg.body,
        actions: msg.actions,
        data   : msg.data,
        icon   : msg.icon,
        dir    : msg.dir,
        image  : msg.image,
        lang   : msg.lang,
        tag    : msg.tag,
        vibrate: msg.vibrate
	};

	return self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener('notificationclick', function(event) {
	
    var notification = event.notification;
    var data         = notification.data;
    var action       = event.action;

    if (action === 'close')
    {
        notification.close();
    }
    else if (action === 'view')
    {
        event.waitUntil(
            clients.openWindow(data.entry_url)
        );
    }
    else
    {
        event.waitUntil(
            clients.openWindow(data.base_url)
        );
    }

}, false);

self.addEventListener('notificationclose', function(event) {

    var notification   = event.notification;
    var notificationId = notification.tag;

    console.log(`Closed notification: ${notificationId}`);
});
