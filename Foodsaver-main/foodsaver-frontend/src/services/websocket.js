import { Client } from '@stomp/stompjs'
import SockJS from 'sockjs-client'

let client = null
let connected = false

export function connect(locationCallback) {
  if (connected) return

  client = new Client({
    webSocketFactory: () => new SockJS(import.meta.env.VITE_API_URL + '/ws'),
    reconnectDelay: 5000,
    onConnect: () => {
      connected = true
      client.subscribe('/topic/location', msg => {
        const data = JSON.parse(msg.body)
        if (locationCallback) locationCallback(data)
      })
    },
    onDisconnect: () => {
      connected = false
    },
    onStompError: () => {},
  })

  client.activate()
}

export function sendLocation(data) {
  if (client?.connected) {
    client.publish({ destination: '/app/location', body: JSON.stringify(data) })
  } else {
    console.warn('WebSocket not connected, cannot send')
  }
}

export function disconnect() {
  if (client) {
    client.deactivate()
    connected = false
  }
}
