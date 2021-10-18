const vapidPublicKey = $("meta[name=vapid_public_key]").attr('content');

registerServiceWorker();

function registerServiceWorker() 
{
    if ('serviceWorker' in navigator && 'PushManager' in window) 
    {
        navigator.serviceWorker.getRegistration()
            .then(function(registration) {

                if (registration)
                {
                    registration.update();
                    subscribeUser(registration);
                }
                else
                {
                    navigator.serviceWorker.register(baseUrl + 'js/service-worker.js')
                        .then(function(newRegistration) {

                            newRegistration.update();
                            subscribeUser(newRegistration);
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

function subscribeUser(registration) 
{
    console.log({registration});

    registration.pushManager.getSubscription()
        .then(function(subscription) {

            if (subscription) 
            {
                storePushSubscription(subscription);
            } 
            else 
            {
                const applicationServerKey = urlB64ToUint8Array(vapidPublicKey);

                registration.pushManager.subscribe({
                    userVisibleOnly      : true,
                    applicationServerKey : applicationServerKey
                })
                    .then(function(newSubscription) {

                        storePushSubscription(newSubscription);
                    })
                    .catch(function(error) {
                        console.error({error});
                    });
            }
        });
}

function urlB64ToUint8Array(base64String) 
{
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64  = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData     = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) 
    {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}


function storePushSubscription(subscription) 
{
    console.log({subscription});

    const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');

    fetch(baseUrl + 'notification', {
        method : 'POST',
        body   : JSON.stringify(subscription),
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
        console.log(res)
    })
    .catch((error) => {
        console.error(error)
    });
}
