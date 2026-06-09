import { useState, useEffect, useRef, useCallback } from 'react'
import { connect, disconnect, sendLocation } from '../../services/websocket'
import { useAuth } from '../../context/AuthContext'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
})

export default function LiveTracking() {
  const { user } = useAuth()
  const isVolunteer = user?.userType === 'volunteer'
  const [locations, setLocations] = useState({})
  const [deliveryId, setDeliveryId] = useState('')
  const [sharing, setSharing] = useState(false)
  const watchIdRef = useRef(null)
  const mapRef = useRef(null)
  const mapInstanceRef = useRef(null)
  const markersRef = useRef({})

  const handleLocationUpdate = useCallback(data => {
    setLocations(prev => ({ ...prev, [data.deliveryId]: { lat: data.lat, lng: data.lng, timestamp: data.timestamp } }))
  }, [])

  useEffect(() => {
    connect(handleLocationUpdate)
    return () => { disconnect(); if (watchIdRef.current) navigator.geolocation.clearWatch(watchIdRef.current) }
  }, [handleLocationUpdate])

  useEffect(() => {
    if (mapInstanceRef.current) return
    const map = L.map(mapRef.current).setView([20, 78], 5)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map)
    mapInstanceRef.current = map
    return () => { map.remove(); mapInstanceRef.current = null }
  }, [])

  useEffect(() => {
    const map = mapInstanceRef.current
    if (!map) return

    const markerIds = new Set(Object.keys(locations))
    Object.keys(markersRef.current).forEach(id => {
      if (!markerIds.has(id)) {
        map.removeLayer(markersRef.current[id])
        delete markersRef.current[id]
      }
    })

    const allLatLngs = []
    Object.entries(locations).forEach(([id, loc]) => {
      const latlng = [parseFloat(loc.lat), parseFloat(loc.lng)]
      allLatLngs.push(latlng)
      if (markersRef.current[id]) {
        markersRef.current[id].setLatLng(latlng)
      } else {
        const marker = L.marker(latlng).addTo(map)
        marker.bindPopup(`<b>Delivery #${id}</b><br/>${loc.lat}, ${loc.lng}`)
        markersRef.current[id] = marker
      }
    })

    if (allLatLngs.length > 0) {
      map.fitBounds(allLatLngs.map(ll => L.latLng(ll[0], ll[1])), { padding: [50, 50], maxZoom: 15 })
    }
  }, [locations])

  const startSharing = () => {
    if (!deliveryId) return alert('Enter a Delivery ID')
    if (!('geolocation' in navigator)) return alert('GPS not available on this device')

    setSharing(true)
    watchIdRef.current = navigator.geolocation.watchPosition(pos => {
      sendLocation({ deliveryId, lat: pos.coords.latitude, lng: pos.coords.longitude })
    }, err => console.error('Geo error', err), { enableHighAccuracy: true })
  }

  const stopSharing = () => {
    if (watchIdRef.current) { navigator.geolocation.clearWatch(watchIdRef.current); watchIdRef.current = null }
    setSharing(false)
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-5xl mx-auto">
        <h1 className="text-3xl font-bold text-gray-800 mb-8">📍 Live Tracking</h1>

        {isVolunteer && (
          <div className="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 className="text-lg font-bold text-gray-700 mb-4">Share Location</h2>
            <div className="flex gap-3 items-end flex-wrap">
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">Delivery ID</label>
                <input value={deliveryId} onChange={e => setDeliveryId(e.target.value)} placeholder="e.g. 6a280607..."
                  disabled={sharing}
                  className="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 w-64" />
              </div>
              {!sharing ? (
                <button onClick={startSharing} className="bg-green-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                  Start Sharing
                </button>
              ) : (
                <button onClick={stopSharing} className="bg-red-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-red-700 transition-colors">
                  Stop Sharing
                </button>
              )}
            </div>
            {sharing && <p className="text-xs text-green-600 mt-2">📍 Sharing your GPS location live...</p>}
          </div>
        )}

        <div ref={mapRef} className="bg-white rounded-xl shadow-md mb-6" style={{ height: '400px' }} />

        <div className="bg-white rounded-xl shadow-md p-6">
          <h2 className="text-lg font-bold text-gray-700 mb-4">Active Locations ({Object.keys(locations).length})</h2>
          {Object.keys(locations).length === 0 ? (
            <p className="text-center text-gray-400 py-8">No locations received yet. They will appear here in real-time.</p>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead><tr className="bg-gray-50 text-gray-600">
                  <th className="px-4 py-3 text-left font-semibold">Delivery ID</th>
                  <th className="px-4 py-3 text-left font-semibold">Latitude</th>
                  <th className="px-4 py-3 text-left font-semibold">Longitude</th>
                  <th className="px-4 py-3 text-left font-semibold">Last Updated</th>
                </tr></thead>
                <tbody>
                  {Object.entries(locations).map(([id, loc]) => (
                    <tr key={id} className="border-t border-gray-100 hover:bg-gray-50">
                      <td className="px-4 py-3 font-medium">#{id}</td>
                      <td className="px-4 py-3 text-gray-600">{loc.lat}</td>
                      <td className="px-4 py-3 text-gray-600">{loc.lng}</td>
                      <td className="px-4 py-3 text-gray-500">{new Date(loc.timestamp).toLocaleTimeString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
