import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { getVolunteerProfile, getVolunteerAvailableDonations, getMyDeliveries, acceptDonation } from '../../services/api'

export default function VolunteerDashboard() {
  const [profile, setProfile] = useState(null)
  const [available, setAvailable] = useState([])
  const [deliveries, setDeliveries] = useState([])
  const [loading, setLoading] = useState(true)
  const [accepting, setAccepting] = useState(null)

  const loadData = () => {
    Promise.all([getVolunteerProfile(), getVolunteerAvailableDonations(), getMyDeliveries()])
      .then(([p, a, d]) => { setProfile(p.data); setAvailable(a.data); setDeliveries(d.data) })
      .catch(console.error)
      .finally(() => setLoading(false))
  }

  useEffect(() => { loadData() }, [])

  const handleAccept = async (id) => {
    setAccepting(id)
    try { await acceptDonation(id); loadData() }
    catch (err) { alert(err.response?.data?.error || 'Failed to accept') }
    finally { setAccepting(null) }
  }

  if (loading) return <div className="min-h-screen flex items-center justify-center"><div className="text-xl text-gray-500 animate-pulse">Loading...</div></div>

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-6xl mx-auto">
        <div className="bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-2xl p-8 mb-8 flex items-center justify-between flex-wrap gap-4">
          <div>
            <h1 className="text-3xl font-bold">Welcome, {profile?.name} 🚚</h1>
            <p className="opacity-90 mt-1">Region: {profile?.region} | Availability: {profile?.availability}</p>
          </div>
          <Link to="/volunteer/activity" className="bg-white text-blue-700 px-5 py-2.5 rounded-xl font-semibold hover:bg-blue-50 transition-colors shadow-md">View Activity</Link>
        </div>

        <div className="grid grid-cols-3 gap-4 mb-8">
          {[
            ['📦', 'Assigned', deliveries.filter(d => d.deliveryStatus === 'Assigned').length, 'bg-yellow-100 text-yellow-700'],
            ['🚗', 'Picked Up', deliveries.filter(d => d.deliveryStatus === 'Picked Up').length, 'bg-blue-100 text-blue-700'],
            ['✅', 'Delivered', deliveries.filter(d => d.deliveryStatus === 'Delivered').length, 'bg-green-100 text-green-700'],
          ].map(([icon, label, value, color]) => (
            <div key={label} className={`${color} rounded-xl p-5 text-center shadow-sm`}>
              <div className="text-3xl mb-1">{icon}</div>
              <div className="text-3xl font-bold">{value}</div>
              <div className="text-sm font-medium mt-1">{label}</div>
            </div>
          ))}
        </div>

        <div className="bg-white rounded-xl shadow-md p-6 mb-6">
          <h2 className="text-lg font-bold text-gray-800 mb-4">🟢 Available Donations</h2>
          {available.length === 0 ? (
            <p className="text-center text-gray-400 py-8">No donations available right now. Check back later.</p>
          ) : (
            <div className="grid md:grid-cols-2 gap-4">
              {available.map(d => (
                <div key={d.donationId} className="border border-gray-200 rounded-xl p-4 hover:border-blue-300 transition-colors">
                  <div className="flex justify-between items-start mb-2">
                    <h3 className="font-semibold text-gray-800">{d.foodType}</h3>
                    <span className="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full font-semibold">PENDING</span>
                  </div>
                  <p className="text-sm text-gray-500 mb-1">📦 {d.quantity}</p>
                  <p className="text-sm text-gray-500 mb-1">📍 {d.address}</p>
                  <p className="text-sm text-gray-500 mb-3">📅 Expires: {d.expiryDate}</p>
                  <button onClick={() => handleAccept(d.donationId)} disabled={accepting === d.donationId}
                    className="w-full bg-blue-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-60 transition-colors">
                    {accepting === d.donationId ? 'Accepting...' : '✓ Accept Delivery'}
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>

        <div className="bg-white rounded-xl shadow-md p-6">
          <h2 className="text-lg font-bold text-gray-800 mb-4">📋 My Deliveries</h2>
          {deliveries.length === 0 ? (
            <p className="text-center text-gray-400 py-8">No deliveries assigned yet.</p>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead><tr className="bg-gray-50 text-gray-600">
                  {['Donation ID', 'OTP', 'Status', 'Created'].map(h => <th key={h} className="px-4 py-3 text-left font-semibold">{h}</th>)}
                </tr></thead>
                <tbody>
                  {deliveries.map(d => (
                    <tr key={d.deliveryId} className="border-t border-gray-100 hover:bg-gray-50">
                      <td className="px-4 py-3">#{d.donationId}</td>
                      <td className="px-4 py-3 font-bold text-purple-700">{d.otpCode}</td>
                      <td className="px-4 py-3">
                        <span className={`px-2 py-1 rounded-full text-xs font-semibold ${d.deliveryStatus === 'Delivered' ? 'bg-green-100 text-green-700' : d.deliveryStatus === 'Picked Up' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700'}`}>
                          {d.deliveryStatus}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-gray-500">{new Date(d.createdAt).toLocaleDateString()}</td>
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
