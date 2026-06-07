import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { registerReceiver } from '../../services/api'

export default function ReceiverRegister() {
  const [form, setForm] = useState({ orgName: '', receiverName: '', phone: '', email: '', password: '', confirmPassword: '', address: '', city: '', state: '', pincode: '' })
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const navigate = useNavigate()

  const handleSubmit = async (e) => {
    e.preventDefault(); setError('')
    if (form.password !== form.confirmPassword) { setError('Passwords do not match'); return }
    setLoading(true)
    try {
      const { confirmPassword, ...data } = form
      await registerReceiver(data)
      navigate('/login/receiver')
    } catch (err) {
      setError(err.response?.data?.message || 'Registration failed.')
    } finally { setLoading(false) }
  }

  const fields = [
    { key: 'orgName', label: 'Organization Name', placeholder: 'e.g. Old Age Home' },
    { key: 'receiverName', label: 'Contact Person', placeholder: 'Full name' },
    { key: 'phone', label: 'Phone', placeholder: '+91 98765 43210', type: 'tel' },
    { key: 'email', label: 'Email', placeholder: 'org@email.com', type: 'email' },
    { key: 'address', label: 'Address', placeholder: 'Street address' },
    { key: 'city', label: 'City', placeholder: 'City' },
    { key: 'state', label: 'State', placeholder: 'State' },
    { key: 'pincode', label: 'Pincode', placeholder: '600001' },
    { key: 'password', label: 'Password', placeholder: '••••••••', type: 'password' },
    { key: 'confirmPassword', label: 'Confirm Password', placeholder: '••••••••', type: 'password' },
  ]

  return (
    <div className="min-h-screen gradient-bg flex items-center justify-center px-4 py-16">
      <div className="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-lg">
        <div className="text-center mb-6">
          <div className="text-5xl mb-3">🏠</div>
          <h2 className="text-2xl font-bold text-gray-800">Organization Registration</h2>
        </div>
        {error && <div className="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4">{error}</div>}
        <form onSubmit={handleSubmit} className="grid grid-cols-2 gap-3">
          {fields.map(({ key, label, placeholder, type = 'text' }) => (
            <div key={key} className={key === 'address' ? 'col-span-2' : ''}>
              <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
              <input type={type} value={form[key]} onChange={e => setForm({ ...form, [key]: e.target.value })}
                required className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm"
                placeholder={placeholder} />
            </div>
          ))}
          <button type="submit" disabled={loading}
            className="col-span-2 bg-orange-500 text-white py-3 rounded-lg font-semibold hover:bg-orange-600 disabled:opacity-60 transition-colors mt-2">
            {loading ? 'Registering...' : 'Create Account'}
          </button>
        </form>
        <p className="text-center text-sm text-gray-500 mt-4">
          Already registered? <Link to="/login/receiver" className="text-orange-500 font-semibold hover:underline">Login</Link>
        </p>
      </div>
    </div>
  )
}
