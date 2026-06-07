import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { getReceiverProfile, getMyRequests, getNotifications } from '../../services/api'
import { useAuth } from '../../context/AuthContext'

export default function ReceiverDashboard() {
  const { user } = useAuth()
  const [profile, setProfile] = useState(null)
  const [requests, setRequests] = useState([])
  const [notifications, setNotifications] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([getReceiverProfile(), getMyRequests(), getNotifications('receiver', user.userId)])
      .then(([p, r, n]) => { setProfile(p.data); setRequests(r.data); setNotifications(n.data) })
      .catch(console.error)
      .finally(() => setLoading(false))
  }, [user.userId])

  if (loading) return <div className="min-h-screen flex items-center justify-center"><div className="text-xl text-gray-500 animate-pulse">Loading...</div></div>

  const stats = [
    { label: 'Total Requests', value: requests.length, color: 'bg-orange-100 text-orange-700', icon: '📋' },
    { label: 'Pending', value: requests.filter(r => r.status === 'Pending').length, color: 'bg-yellow-100 text-yellow-700', icon: '⏳' },
    { label: 'Approved', value: requests.filter(r => r.status === 'Approved').length, color: 'bg-blue-100 text-blue-700', icon: '✓' },
    { label: 'Completed', value: requests.filter(r => r.status === 'Completed').length, color: 'bg-green-100 text-green-700', icon: '✅' },
  ]

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-6xl mx-auto">
        <div className="bg-gradient-to-r from-orange-500 to-orange-700 text-white rounded-2xl p-8 mb-8 flex items-center justify-between flex-wrap gap-4">
          <div>
            <h1 className="text-3xl font-bold">Welcome, {profile?.orgName} 🏠</h1>
            <p className="opacity-90 mt-1">Contact: {profile?.receiverName} | {profile?.city}, {profile?.state}</p>
          </div>
          <Link to="/receiver/browse" className="bg-white text-orange-700 px-6 py-3 rounded-xl font-semibold hover:bg-orange-50 transition-colors shadow-md">
            Browse Donations
          </Link>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          {stats.map(({ label, value, color, icon }) => (
            <div key={label} className={`${color} rounded-xl p-5 text-center shadow-sm`}>
              <div className="text-3xl mb-1">{icon}</div>
              <div className="text-3xl font-bold">{value}</div>
              <div className="text-sm font-medium mt-1">{label}</div>
            </div>
          ))}
        </div>

        {notifications.length > 0 && (
          <div className="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 className="text-lg font-bold text-gray-800 mb-4">🔔 Notifications</h2>
            <div className="space-y-2 max-h-48 overflow-y-auto">
              {notifications.slice(0, 5).map(n => (
                <div key={n.notificationId} className="flex items-start gap-3 bg-orange-50 rounded-lg p-3">
                  <span className="text-orange-500">📢</span>
                  <div>
                    <p className="text-sm text-gray-700">{n.message}</p>
                    <p className="text-xs text-gray-400 mt-0.5">{new Date(n.createdAt).toLocaleString()}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        <div className="bg-white rounded-xl shadow-md p-6">
          <h2 className="text-lg font-bold text-gray-800 mb-4">📋 My Food Requests</h2>
          {requests.length === 0 ? (
            <div className="text-center py-12 text-gray-400">
              <div className="text-5xl mb-3">🍽️</div>
              <p>No requests yet.</p>
              <Link to="/receiver/browse" className="text-orange-500 font-semibold hover:underline mt-2 block">Browse available donations →</Link>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead><tr className="bg-gray-50 text-gray-600">
                  {['Request ID', 'Donation ID', 'Qty', 'Status', 'Receiver OTP', 'Date'].map(h => (
                    <th key={h} className="px-4 py-3 text-left font-semibold">{h}</th>
                  ))}
                </tr></thead>
                <tbody>
                  {requests.map(r => (
                    <tr key={r.requestId} className="border-t border-gray-100 hover:bg-gray-50">
                      <td className="px-4 py-3">#{r.requestId}</td>
                      <td className="px-4 py-3">#{r.donationId}</td>
                      <td className="px-4 py-3">{r.quantity}</td>
                      <td className="px-4 py-3">
                        <span className={`px-2 py-1 rounded-full text-xs font-semibold ${r.status === 'Completed' ? 'bg-green-100 text-green-700' : r.status === 'Approved' ? 'bg-blue-100 text-blue-700' : r.status === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'}`}>
                          {r.status}
                        </span>
                      </td>
                      <td className="px-4 py-3 font-bold text-orange-700">{r.receiverOtp || '—'}</td>
                      <td className="px-4 py-3 text-gray-500">{new Date(r.requestDate).toLocaleDateString()}</td>
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
