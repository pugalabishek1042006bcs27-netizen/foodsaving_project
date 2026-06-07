import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { uploadDonation } from '../../services/api'

export default function UploadDonation() {
  const navigate = useNavigate()
  const [form, setForm] = useState({ foodType: '', quantity: '', expiryDate: '', preparationStatus: 'Ready to Eat', dietaryOptions: '', allergens: '', description: '', contactPhone: '', contactEmail: '', address: '' })
  const [images, setImages] = useState([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [successOtp, setSuccessOtp] = useState(null)

  const handleSubmit = async (e) => {
    e.preventDefault(); setError(''); setLoading(true)
    try {
      const data = new FormData()
      Object.entries(form).forEach(([k, v]) => data.append(k, v))
      images.forEach(img => data.append('images', img))
      const res = await uploadDonation(data)
      setSuccessOtp(res.data.otpCode)
      setTimeout(() => navigate('/donor/dashboard'), 5000)
    } catch (err) {
      setError(err.response?.data?.message || 'Upload failed. Try again.')
    } finally { setLoading(false) }
  }

  if (successOtp) return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 px-4">
      <div className="bg-white rounded-2xl shadow-xl p-10 text-center max-w-sm">
        <div className="text-6xl mb-4">🎉</div>
        <h2 className="text-2xl font-bold text-green-600 mb-2">Donation Uploaded!</h2>
        <p className="text-gray-500 mb-4">Share this OTP with the volunteer for pickup verification:</p>
        <div className="bg-green-50 border-2 border-green-300 rounded-xl p-4 mb-4">
          <p className="text-sm text-gray-500 mb-1">Pickup OTP</p>
          <p className="text-4xl font-bold text-green-700 tracking-widest">{successOtp}</p>
        </div>
        <p className="text-xs text-gray-400">Redirecting to dashboard in 5 seconds...</p>
      </div>
    </div>
  )

  return (
    <div className="min-h-screen bg-gray-50 py-10 px-4">
      <div className="max-w-2xl mx-auto">
        <div className="bg-white rounded-2xl shadow-lg p-8">
          <h2 className="text-2xl font-bold text-gray-800 mb-1">🍱 Upload Food Donation</h2>
          <p className="text-gray-500 text-sm mb-6">Fill in the details of your food donation</p>
          {error && <div className="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4">{error}</div>}
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Food Type *</label>
                <input value={form.foodType} onChange={e => setForm({ ...form, foodType: e.target.value })} required
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" placeholder="e.g. Rice, Biryani" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                <input value={form.quantity} onChange={e => setForm({ ...form, quantity: e.target.value })} required
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" placeholder="e.g. 50 plates" />
              </div>
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Expiry Date *</label>
                <input type="date" value={form.expiryDate} onChange={e => setForm({ ...form, expiryDate: e.target.value })} required
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Preparation Status</label>
                <select value={form.preparationStatus} onChange={e => setForm({ ...form, preparationStatus: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
                  {['Ready to Eat', 'Needs Reheating', 'Raw Ingredients'].map(s => <option key={s}>{s}</option>)}
                </select>
              </div>
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Dietary Options</label>
                <input value={form.dietaryOptions} onChange={e => setForm({ ...form, dietaryOptions: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" placeholder="Vegan, Halal, etc." />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Allergens</label>
                <input value={form.allergens} onChange={e => setForm({ ...form, allergens: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" placeholder="Nuts, Gluten, etc." />
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea value={form.description} onChange={e => setForm({ ...form, description: e.target.value })} rows={3}
                className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" placeholder="Brief description..." />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                <input type="tel" value={form.contactPhone} onChange={e => setForm({ ...form, contactPhone: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                <input type="email" value={form.contactEmail} onChange={e => setForm({ ...form, contactEmail: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" />
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Pickup Address *</label>
              <textarea value={form.address} onChange={e => setForm({ ...form, address: e.target.value })} required rows={2}
                className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" placeholder="Full pickup address" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Food Images</label>
              <input type="file" multiple accept="image/*" onChange={e => setImages([...e.target.files])}
                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
              {images.length > 0 && <p className="text-xs text-gray-400 mt-1">{images.length} image(s) selected</p>}
            </div>
            <div className="flex gap-3 pt-2">
              <button type="submit" disabled={loading}
                className="flex-1 bg-green-600 text-white py-3 rounded-xl font-semibold hover:bg-green-700 disabled:opacity-60 transition-colors">
                {loading ? 'Uploading...' : '🚀 Upload Donation'}
              </button>
              <button type="button" onClick={() => navigate('/donor/dashboard')}
                className="px-6 py-3 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  )
}
