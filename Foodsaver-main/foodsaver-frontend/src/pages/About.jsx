export default function About() {
  return (
    <div className="min-h-screen bg-gray-50 py-16 px-4">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-4xl font-bold text-center text-purple-700 mb-4">About FoodSavr</h1>
        <p className="text-center text-gray-500 mb-12 text-lg">Our mission to end food waste and hunger</p>

        <div className="bg-white rounded-2xl shadow-lg p-8 mb-8">
          <h2 className="text-2xl font-bold text-gray-800 mb-4">🌍 Our Mission</h2>
          <p className="text-gray-600 leading-relaxed">
            FoodSavr is a platform that bridges the gap between food surplus and food scarcity. Every day, tonnes of
            perfectly good food goes to waste while millions go hungry. We connect donors — restaurants, grocery stores,
            individuals — with those in need through a network of dedicated volunteers.
          </p>
        </div>

        <h2 className="text-2xl font-bold text-center text-gray-800 mb-8">How It Works</h2>
        <div className="grid md:grid-cols-3 gap-6 mb-12">
          {[
            { step: '1', icon: '🍱', title: 'Donor Lists Food', desc: 'A donor uploads available food with type, quantity, and pickup address.' },
            { step: '2', icon: '🚗', title: 'Volunteer Picks Up', desc: 'A nearby volunteer accepts the delivery and verifies pickup with an OTP.' },
            { step: '3', icon: '🤝', title: 'Receiver Gets Food', desc: 'The food reaches a shelter or food bank, completing the chain of kindness.' },
          ].map(({ step, icon, title, desc }) => (
            <div key={step} className="bg-white rounded-xl shadow-md p-6 text-center card-hover">
              <div className="w-10 h-10 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">{step}</div>
              <div className="text-4xl mb-3">{icon}</div>
              <h3 className="font-bold text-gray-800 mb-2">{title}</h3>
              <p className="text-gray-500 text-sm leading-relaxed">{desc}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
