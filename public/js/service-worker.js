self.addEventListener('push', function (e) {

    if (!(self.Notification && self.Notification.permission === 'granted')) 
    {
        console.warn('notifications aren\'t supported or permission not granted!');

        return;
    }

    if (e.data) 
    {
        var msg = e.data.json();
        console.log({msg});

        var options = {
            body    : msg.body,
            icon    : msg.icon,
            image   : msg.image,
            lang    : msg.lang,
            dir     : msg.dir,
            // actions : [
            //     {action: 'view', title: 'View entry', icon: 'check.png'},
            //     {action: 'close', title: 'No thanks', icon: 'x.png'},
            // ],
            actions : msg.actions,
            data    : msg.data,
            tag     : msg.tag,
            vibrate : msg.vibrate,
        };
        
        e.waitUntil(
            self.registration.showNotification(msg.title, options)
        );
    }
});

self.addEventListener('notificationclick', function (event) {
    
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

self.addEventListener('notificationclose', function (event) {

    var notification   = event.notification;
    var notificationId = notification.tag;

    console.log(`Closed notification: ${notificationId}`);
});
