// preload.js
const { contextBridge, ipcRenderer } = require('electron');

// Az ipcRenderer küldése és fogadása a renderer folyamatról
contextBridge.exposeInMainWorld('electron', {
    // Bejelentkezési adat küldése
    sendLoginData: (email, password) => ipcRenderer.send('login', email, password),

    // Bejelentkezés sikeres esemény
    onLoginSuccess: (callback) => ipcRenderer.on('login-success', (event, user) => {
        callback(user); // A sikeres bejelentkezés után meghívja a callback-et
    }),

    // Bejelentkezés sikertelen esemény
    onLoginFailed: (callback) => ipcRenderer.on('login-failed', (event, message) => {
        callback(message); // A sikertelen bejelentkezés után meghívja a callback-et
    }),

    // Kijelentkezés esemény küldése
    sendLogout: () => ipcRenderer.send('logout')
});
