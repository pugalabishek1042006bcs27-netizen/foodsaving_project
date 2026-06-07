export default function Services() {
  const services = [
    { icon: '🏪', title: 'Food Donation', desc: 'Easily list surplus food with photos, quantity, expiry, and dietary details.', features: ['Image upload', 'OTP verification', 'Real-time tracking'] },
    { icon: '🚚', title: 'Volunteer Delivery', desc: 'Volunteers browse available donations, accept deliveries, and track activity.', features: ['GPS tracking', 'Delivery history', 'OTP pickup verification'] },
    { icon: '🏠', title: 'Food Access', desc: 'Registered organizations browse available food donations and make requests.', features: ['Browse donations', 'Certificate upload', 'Request management'] },
    { icon: '🛡️', title: 'Admin Management', desc: 'Full control over users, donations, volunteers, and certificates.', features: ['User management', 'Donation oversight', 'Certificate approval'] },
    { icon: '🔔', title: 'Notifications', desc: 'Stay updated with instant alerts about donation status and delivery updates.', features: ['Instant alerts', 'Status updates', 'Delivery confirmations'] },
    { icon: '📊', title: 'Analytics', desc: 'Track impact with statistics on donations made and communities helped.', features: ['Donation stats', 'Impact metrics', 'Activity history'] },
  ]

  return (
    <div className="min-h-screen bg-gray-50 py-16 px-4">
      <div className="max-w-6xl mx-auto">
        <h1 className="text-4xl font-bold text-center text-purple-700 mb-4">Our Services</h1>
        <p className="text-center text-gray-500 mb-12 text-lg">Everything you need to donate, deliver, and receive food</p>
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          {services.map(({ icon, title, desc, features }) => (
            <div key={title} className="bg-white rounded-xl shadow-md p-6 card-hover">
              <div className="text-4xl mb-4">{icon}</div>
              <h3 className="text-xl font-bold text-gray-800 mb-3">{title}</h3>
              <p className="text-gray-500 text-sm mb-4 leading-relaxed">{desc}</p>
              <ul className="space-y-1">
                {features.map(f => (
                  <li key={f} className="text-sm text-green-600 flex items-center gap-2">
                    <span>✓</span>{f}
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
