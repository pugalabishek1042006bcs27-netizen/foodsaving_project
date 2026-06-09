import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { getDonorProfile, getDonorDonations, getNotifications } from '../../services/api'
import { useAuth } from '../../context/AuthContext'

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-800',
  accepted: 'bg-blue-100 text-blue-800',
  in_progress: 'bg-indigo-100 text-indigo-800',
  completed: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
}

export default function DonorDashboard() {
  const { user } = useAuth()
  const [profile, setProfile] = useState(null)
  const [donations, setDonations] = useState([])
  const [notifications, setNotifications] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([getDonorProfile(), getDonorDonations(), getNotifications('donor', user.userId)])
      .then(([p, d, n]) => { setProfile(p.data); setDonations(d.data); setNotifications(n.data) })
      .catch(console.error)
      .finally(() => setLoading(false))
  }, [user.userId])

  if (loading) return <div className="min-h-screen flex items-center justify-center"><div className="text-xl text-gray-500 animate-pulse">Loading...</div></div>

  const stats = [
    { label: 'Total', value: donations.length, color: 'bg-purple-100 text-purple-700', icon: '📦' },
    { label: 'Pending', value: donations.filter(d => d.status === 'pending').length, color: 'bg-yellow-100 text-yellow-700', icon: '⏳' },
    { label: 'In Progress', value: donations.filter(d => d.status === 'in_progress').length, color: 'bg-blue-100 text-blue-700', icon: '🚚' },
    { label: 'Completed', value: donations.filter(d => d.status === 'completed').length, color: 'bg-green-100 text-green-700', icon: '✅' },
  ]

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-6xl mx-auto">
        <div className="bg-gradient-to-r from-green-500 to-green-700 text-white rounded-2xl p-8 mb-8 flex items-center justify-between flex-wrap gap-4">
          <div>
            <h1 className="text-3xl font-bold">Welcome, {profile?.name} 🌿</h1>
            <p className="opacity-90 mt-1">Thank you for making a difference. Manage your donations below.</p>
          </div>
          <div className="flex gap-3">
            <Link to="/tracking" className="bg-white text-blue-700 px-5 py-2.5 rounded-xl font-semibold hover:bg-blue-50 transition-colors shadow-md">📍 Live Tracking</Link>
            <Link to="/donor/upload" className="bg-white text-green-700 px-6 py-3 rounded-xl font-semibold hover:bg-green-50 transition-colors shadow-md">+ Upload Donation</Link>
          </div>
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
            <h2 className="text-lg font-bold text-gray-800 mb-4">🔔 Recent Notifications</h2>
            <div className="space-y-2 max-h-48 overflow-y-auto">
              {notifications.slice(0, 5).map(n => (
                <div key={n.notificationId} className="flex items-start gap-3 bg-purple-50 rounded-lg p-3">
                  <span className="text-purple-500 mt-0.5">📢</span>
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
          <h2 className="text-lg font-bold text-gray-800 mb-4">📋 Donation History</h2>
          {donations.length === 0 ? (
            <div className="text-center py-12 text-gray-400">
              <div className="text-5xl mb-3">📭</div>
              <p>No donations yet.</p>
              <Link to="/donor/upload" className="text-green-600 font-semibold hover:underline mt-2 block">Upload your first donation →</Link>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="bg-gray-50 text-gray-600">
                    {['ID', 'Food Type', 'Quantity', 'OTP Code', 'Status', 'Expiry', 'Uploaded'].map(h => (
                      <th key={h} className="px-4 py-3 text-left font-semibold">{h}</th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {donations.map(d => (
                    <tr key={d.donationId} className="border-t border-gray-100 hover:bg-gray-50">
                      <td className="px-4 py-3 font-mono text-gray-500">#{d.donationId}</td>
                      <td className="px-4 py-3 font-medium">{d.foodType}</td>
                      <td className="px-4 py-3">{d.quantity}</td>
                      <td className="px-4 py-3">
                        <span className="font-bold text-purple-700 bg-purple-50 px-2 py-1 rounded">{d.otpCode || '—'}</span>
                      </td>
                      <td className="px-4 py-3">
                        <span className={`px-2 py-1 rounded-full text-xs font-semibold uppercase ${statusColors[d.status] || 'bg-gray-100 text-gray-700'}`}>{d.status}</span>
                      </td>
                      <td className="px-4 py-3 text-gray-500">{d.expiryDate}</td>
                      <td className="px-4 py-3 text-gray-500">{new Date(d.uploadDate).toLocaleDateString()}</td>
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
