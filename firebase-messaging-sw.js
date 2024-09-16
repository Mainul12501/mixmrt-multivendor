importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js");

firebase.initializeApp({
apiKey: "AIzaSyDwdLNqrd8Is1i_q8BQWxXVbJshwHzAdsg",
  authDomain: "secret-lambda-403915.firebaseapp.com",
  databaseURL: "https://secret-lambda-403915-default-rtdb.firebaseio.com",
  projectId: "secret-lambda-403915",
  storageBucket: "secret-lambda-403915.appspot.com",
  messagingSenderId: "215254213048",
  appId: "1:215254213048:web:57383bb9db3677c6556f4d",
  measurementId: "G-R8QTGD955W"
});

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
    const promiseChain = clients
        .matchAll({
            type: "window",
            includeUncontrolled: true
        })
        .then(windowClients => {
            for (let i = 0; i < windowClients.length; i++) {
                const windowClient = windowClients[i];
                windowClient.postMessage(payload);
            }
        })
        .then(() => {
            const title = payload.notification.title;
            const options = {
                body: payload.notification.score
              };
            return registration.showNotification(title, options);
        });
    return promiseChain;
});
self.addEventListener('notificationclick', function (event) {
    console.log('notification received: ', event)
});
