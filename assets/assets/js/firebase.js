
/* ==========================================
   RIZA AI - FIREBASE CONFIGURATION
   Authentication + Firestore Database
========================================== */

"use strict";

/* 1. FIREBASE CONFIGURATION
   Replace these values with your Firebase
   project configuration.
*/

const firebaseConfig = {
    apiKey: "YOUR_FIREBASE_API_KEY",
    authDomain: "YOUR_PROJECT_ID.firebaseapp.com",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_PROJECT_ID.firebasestorage.app",
    messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
    appId: "YOUR_FIREBASE_APP_ID"
};

/* 2. CHECK FIREBASE SDK */

if (typeof firebase === "undefined") {
    console.error("Firebase SDK not loaded. Check index.html.");
} else if (!firebase.apps.length) {

    /* 3. INITIALIZE FIREBASE */

    firebase.initializeApp(firebaseConfig);

    /* 4. CONNECT SERVICES */

    window.RIZA_FIREBASE = {
        auth: firebase.auth(),
        db: firebase.firestore()
    };

    console.log("RIZA Firebase connected successfully.");

} else {

    window.RIZA_FIREBASE = {
        auth: firebase.auth(),
        db: firebase.firestore()
    };

    console.log("RIZA Firebase already initialized.");
}
