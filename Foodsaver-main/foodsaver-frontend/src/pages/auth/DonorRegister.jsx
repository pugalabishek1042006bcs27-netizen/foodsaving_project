import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { registerDonor } from '../../services/api'

export default function DonorRegister() {
  const [form, setForm] = useState({ name: '', email: '', password: '', confirmPassword: '', contactNumber: '', address: '' })
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const navigate = useNavigate()

  const handleSubmit = async (e) => {
    e.preventDefault(); setError('')
    if (form.password !== form.confirmPassword) { setError('Passwords do not match'); return }
    setLoading(true)
    try {
      await registerDonor({ name: form.name, email: form.email, password: form.password, contactNumber: form.contactNumber, address: form.address })
      navigate('/login/donor')
    } catch (err) {
      setError(err.response?.data?.message || 'Registration failed.')
    } finally { setLoading(false) }
  }

  const fields = [
    { key: 'name', label: 'Full Name', type: 'text', placeholder: 'Your full name' },
    { key: 'email', label: 'Email', type: 'email', placeholder: 'your@email.com' },
    { key: 'contactNumber', label: 'Contact Number', type: 'tel', placeholder: '+91 98765 43210' },
    { key: 'address', label: 'Address', type: 'text', placeholder: 'Your address' },
    { key: 'password', label: 'Password', type: 'password', placeholder: '••••••••' },
    { key: 'confirmPassword', label: 'Confirm Password', type: 'password', placeholder: '••••••••' },
  ]

  return (
    <div className="min-h-screen gradient-bg flex items-center justify-center px-4 py-16">
      <div className="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div className="text-center mb-6">
          <div className="text-5xl mb-3">🏪</div>
          <h2 className="text-2xl font-bold text-gray-800">Donor Registration</h2>
          <p className="text-gray-500 text-sm mt-1">Create an account to start donating food</p>
        </div>
        {error && <div className="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4">{error}</div>}
        <form onSubmit={handleSubmit} className="space-y-3">
          {fields.map(({ key, label, type, placeholder }) => (
            <div key={key}>
              <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
              <input type={type} value={form[key]} onChange={e => setForm({ ...form, [key]: e.target.value })}
                required className="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400"
                placeholder={placeholder} />
            </div>
          ))}
          <button type="submit" disabled={loading}
            className="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 disabled:opacity-60 transition-colors mt-2">
            {loading ? 'Registering...' : 'Create Account'}
          </button>
        </form>
        <p className="text-center text-sm text-gray-500 mt-4">
          Already have an account? <Link to="/login/donor" className="text-purple-600 font-semibold hover:underline">Login</Link>
        </p>
      </div>
    </div>
  )
}
