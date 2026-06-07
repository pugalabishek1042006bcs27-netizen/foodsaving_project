import { useState } from 'react'
import { submitContact } from '../services/api'

export default function Contact() {
  const [form, setForm] = useState({ name: '', email: '', message: '' })
  const [loading, setLoading] = useState(false)
  const [success, setSuccess] = useState(false)
  const [error, setError] = useState('')

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true); setError('')
    try {
      await submitContact(form)
      setSuccess(true)
      setForm({ name: '', email: '', message: '' })
    } catch {
      setError('Failed to send message. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gray-50 py-16 px-4 flex items-center justify-center">
      <div className="max-w-lg w-full">
        <h1 className="text-4xl font-bold text-center text-purple-700 mb-2">Contact Us</h1>
        <p className="text-center text-gray-500 mb-8">We'd love to hear from you</p>

        <div className="bg-white rounded-2xl shadow-lg p-8">
          {success ? (
            <div className="text-center py-8">
              <div className="text-5xl mb-4">✅</div>
              <h3 className="text-xl font-bold text-green-600 mb-2">Message Sent!</h3>
              <p className="text-gray-500">Thank you for reaching out. We'll get back to you soon.</p>
              <button onClick={() => setSuccess(false)} className="mt-4 text-purple-600 hover:underline">
                Send another message
              </button>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              {error && <div className="bg-red-50 text-red-600 p-3 rounded-lg text-sm">{error}</div>}
              {[
                { key: 'name', label: 'Name', type: 'text', placeholder: 'Your name' },
                { key: 'email', label: 'Email', type: 'email', placeholder: 'your@email.com' },
              ].map(({ key, label, type, placeholder }) => (
                <div key={key}>
                  <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
                  <input type={type} value={form[key]} onChange={e => setForm({ ...form, [key]: e.target.value })}
                    required className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-400"
                    placeholder={placeholder} />
                </div>
              ))}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea value={form.message} onChange={e => setForm({ ...form, message: e.target.value })}
                  required rows={5} className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-400"
                  placeholder="Your message..." />
              </div>
              <button type="submit" disabled={loading}
                className="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 disabled:opacity-60 transition-colors">
                {loading ? 'Sending...' : 'Send Message'}
              </button>
            </form>
          )}
        </div>

        <div className="mt-8 grid grid-cols-3 gap-4 text-center">
          {[['📧', 'Email', 'support@foodsavr.com'], ['📞', 'Phone', '+91 98765 43210'], ['📍', 'Location', 'Tamil Nadu, India']].map(([icon, label, value]) => (
            <div key={label} className="bg-white rounded-xl shadow p-4">
              <div className="text-2xl mb-1">{icon}</div>
              <div className="text-xs font-semibold text-gray-700">{label}</div>
              <div className="text-xs text-gray-500 mt-1">{value}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
