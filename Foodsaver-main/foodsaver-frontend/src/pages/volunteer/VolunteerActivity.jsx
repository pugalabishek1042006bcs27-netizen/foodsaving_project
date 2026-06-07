import { useState, useEffect } from 'react'
import { getMyDeliveries, verifyPickup } from '../../services/api'

export default function VolunteerActivity() {
  const [deliveries, setDeliveries] = useState([])
  const [loading, setLoading] = useState(true)
  const [otp, setOtp] = useState({ donationId: '', otp: '' })
  const [msg, setMsg] = useState('')

  useEffect(() => {
    getMyDeliveries().then(r => setDeliveries(r.data)).finally(() => setLoading(false))
  }, [])

  const handleVerifyPickup = async (e) => {
    e.preventDefault()
    try {
      await verifyPickup({ donationId: otp.donationId, otp: otp.otp })
      setMsg('✅ Pickup verified successfully!')
      setOtp({ donationId: '', otp: '' })
      getMyDeliveries().then(r => setDeliveries(r.data))
    } catch (err) {
      setMsg('❌ ' + (err.response?.data?.message || 'Invalid OTP'))
    }
  }

  if (loading) return <div className="min-h-screen flex items-center justify-center"><div className="text-xl text-gray-500 animate-pulse">Loading...</div></div>

  return (
    <div className="min-h-screen bg-gray-50 py-10 px-4">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold text-gray-800 mb-8">📊 Delivery Activity</h1>

        <div className="bg-white rounded-xl shadow-md p-6 mb-6">
          <h2 className="text-lg font-bold text-gray-700 mb-4">🔐 Verify Pickup (Enter Donor OTP)</h2>
          {msg && (
            <div className={`p-3 rounded-lg text-sm mb-4 ${msg.startsWith('✅') ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'}`}>{msg}</div>
          )}
          <form onSubmit={handleVerifyPickup} className="flex gap-3 flex-wrap">
            <input value={otp.donationId} onChange={e => setOtp({ ...otp, donationId: e.target.value })}
              placeholder="Donation ID" required
              className="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 w-32" />
            <input value={otp.otp} onChange={e => setOtp({ ...otp, otp: e.target.value })}
              placeholder="Enter OTP" required
              className="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 w-36" />
            <button type="submit" className="bg-blue-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
              Verify Pickup
            </button>
          </form>
        </div>

        <div className="bg-white rounded-xl shadow-md p-6">
          <h2 className="text-lg font-bold text-gray-700 mb-4">All Deliveries ({deliveries.length})</h2>
          {deliveries.length === 0 ? (
            <p className="text-center text-gray-400 py-8">No delivery history yet.</p>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead><tr className="bg-gray-50 text-gray-600">
                  {['Delivery ID', 'Donation', 'OTP', 'Status', 'Picked At', 'Delivered At'].map(h => (
                    <th key={h} className="px-4 py-3 text-left font-semibold">{h}</th>
                  ))}
                </tr></thead>
                <tbody>
                  {deliveries.map(d => (
                    <tr key={d.deliveryId} className="border-t border-gray-100 hover:bg-gray-50">
                      <td className="px-4 py-3">#{d.deliveryId}</td>
                      <td className="px-4 py-3">#{d.donationId}</td>
                      <td className="px-4 py-3 font-bold text-purple-700">{d.otpCode}</td>
                      <td className="px-4 py-3">
                        <span className={`px-2 py-1 rounded-full text-xs font-semibold ${d.deliveryStatus === 'Delivered' ? 'bg-green-100 text-green-700' : d.deliveryStatus === 'Picked Up' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700'}`}>
                          {d.deliveryStatus}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-gray-500">{d.pickedAt ? new Date(d.pickedAt).toLocaleString() : '—'}</td>
                      <td className="px-4 py-3 text-gray-500">{d.deliveredAt ? new Date(d.deliveredAt).toLocaleString() : '—'}</td>
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
