const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const axios = require('axios');

let mainWindow;
let loginWindow;
let adminWindow;

function createWindow() {
    loginWindow = new BrowserWindow({
        width: 400,
        height: 300,
        webPreferences: {
            nodeIntegration: true
        }
    });

    loginWindow.loadFile('login.html');
    loginWindow.on('closed', () => {
        loginWindow = null;
    });
}

// Bejelentkezés kezelése
ipcMain.handle('login', async (event, email, password) => {
    try {
        const response = await axios.post('http://localhost/api.php/login', { email, password });
        return response.data;
    } catch (error) {
        console.error('Login error:', error);
        return { success: false, message: 'Hiba történt a bejelentkezés során.' };
    }
});

// Termékek lekérése admin felülethez
ipcMain.handle('fetch-products', async () => {
    try {
        const response = await axios.get('http://localhost/api.php/orders');
        return response.data;
    } catch (error) {
        console.error('Fetch error:', error);
        return { success: false, message: 'Nem sikerült a termékek lekérése.' };
    }
});

// Admin ablak megnyitása
ipcMain.handle('open-admin', () => {
    if (adminWindow) {
        adminWindow.focus();
        return;
    }

    adminWindow = new BrowserWindow({
        width: 800,
        height: 600,
        webPreferences: {
            nodeIntegration: true
        }
    });

    adminWindow.loadFile('admin.html');
    adminWindow.on('closed', () => {
        adminWindow = null;
    });
});

app.on('ready', createWindow);

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});
