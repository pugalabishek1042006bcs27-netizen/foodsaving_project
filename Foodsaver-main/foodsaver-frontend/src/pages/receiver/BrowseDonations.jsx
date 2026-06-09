import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { getAvailableDonations, requestDonation } from '../../services/api'

export default function BrowseDonations() {
  const [donations, setDonations] = useState([])
  const [loading, setLoading] = useState(true)
  const [requesting, setRequesting] = useState(null)
  const [msg, setMsg] = useState('')
  const navigate = useNavigate()

  useEffect(() => {
    getAvailableDonations().then(r => setDonations(r.data)).finally(() => setLoading(false))
  }, [])

  const handleRequest = async (donationId) => {
    if (!window.confirm('Request this donation for your organization?')) return
    setRequesting(donationId)
    try {
      await requestDonation(donationId, { details: 'Requested from available list', quantity: 1 })
      setMsg('✅ Request submitted successfully!')
      setDonations(prev => prev.filter(d => d.donationId !== donationId))
    } catch (err) {
      setMsg('❌ ' + (err.response?.data?.message || 'Request failed'))
    } finally { setRequesting(null) }
  }

  if (loading) return <div className="min-h-screen flex items-center justify-center"><div className="text-xl text-gray-500 animate-pulse">Loading...</div></div>

  return (
    <div className="min-h-screen bg-gray-50 py-10 px-4">
      <div className="max-w-6xl mx-auto">
        <div className="flex items-center justify-between mb-8 flex-wrap gap-4">
          <div>
            <h1 className="text-3xl font-bold text-gray-800">🍽️ Available Donations</h1>
            <p className="text-gray-500 mt-1">{donations.length} donation(s) available</p>
          </div>
          <button onClick={() => navigate('/receiver/dashboard')}
            className="bg-orange-100 text-orange-700 px-5 py-2.5 rounded-xl font-semibold hover:bg-orange-200 transition-colors">
            ← Back to Dashboard
          </button>
        </div>

        {msg && (
          <div className={`p-4 rounded-xl text-sm mb-6 ${msg.startsWith('✅') ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'}`}>{msg}</div>
        )}

        {donations.length === 0 ? (
          <div className="text-center py-20 text-gray-400">
            <div className="text-6xl mb-4">🍽️</div>
            <p className="text-xl">No donations available right now.</p>
            <p className="mt-2 text-sm">Check back later for new donations.</p>
          </div>
        ) : (
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            {donations.map(d => (
              <div key={d.donationId} className="bg-white rounded-xl shadow-md overflow-hidden card-hover">
                {d.imagePaths && d.imagePaths.split(',')[0] && (
                  <img src={`${import.meta.env.VITE_API_URL}/${d.imagePaths.split(',')[0]}`} alt={d.foodType}
                    className="w-full h-40 object-cover" onError={e => e.target.style.display = 'none'} />
                )}
                <div className="p-5">
                  <div className="flex justify-between items-start mb-3">
                    <h3 className="font-bold text-gray-800 text-lg">{d.foodType}</h3>
                    <span className="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full font-semibold">Available</span>
                  </div>
                  <div className="space-y-1.5 text-sm text-gray-600 mb-4">
                    <p>📦 <span className="font-medium">{d.quantity}</span></p>
                    <p>📅 Expires: {d.expiryDate}</p>
                    {d.dietaryOptions && <p>🥗 {d.dietaryOptions}</p>}
                    {d.description && <p className="text-gray-500 text-xs line-clamp-2">{d.description}</p>}
                    <p className="text-xs text-gray-400">📍 {d.address}</p>
                  </div>
                  <button onClick={() => handleRequest(d.donationId)} disabled={requesting === d.donationId}
                    className="w-full bg-orange-500 text-white py-2.5 rounded-lg font-semibold hover:bg-orange-600 disabled:opacity-60 transition-colors">
                    {requesting === d.donationId ? 'Requesting...' : '🤝 Request This Donation'}
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}
