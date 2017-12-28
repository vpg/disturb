const {app, BrowserWindow, dialog } = require('electron')
const path = require('path')
const url = require('url')

let building = false
let mainWindow

function createWindow () {
    mainWindow = new BrowserWindow(
        {
            width: 1200,
            height: 1000,
            'min-width': 500,
            'min-height': 200,
            'accept-first-mouse': true,
            'title-bar-style': 'hidden'
        }
    )

    mainWindow.loadURL(url.format({
        pathname: path.join(__dirname, 'index.html'),
        protocol: 'file:',
        slashes: true
    }))

    mainWindow.webContents.openDevTools()
    mainWindow.on('closed', () => {
        mainWindow = null
    })
}

app.on('ready', createWindow)

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit()
    }
})

app.on('activate', () => {
    if (mainWindow === null) {
        createWindow()
    }
})
