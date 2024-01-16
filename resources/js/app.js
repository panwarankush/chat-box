require('./bootstrap');

import Alpine from 'alpinejs';
// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';

window.Alpine = Alpine;

Alpine.start();


// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     wsHost: window.location.hostname,
//     wsPort: 6001,
//     forceTLS: false,
//     disableStats: true,
//     enabledTransports: ['ws'],
// });
