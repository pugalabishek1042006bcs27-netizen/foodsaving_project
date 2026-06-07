import { Link } from 'react-router-dom'

export default function Home() {
  return (
    <div className="gradient-bg min-h-screen flex items-center justify-center px-4 py-20">
      <div className="text-center text-white fade-in max-w-5xl mx-auto">
        <h1 className="text-5xl md:text-6xl font-bold mb-6 leading-tight">
          Welcome To Food Saving Management
        </h1>
        <p className="text-xl mb-10 max-w-2xl mx-auto opacity-90">
          Connecting food donors with those in need. Together, we can reduce food waste and fight hunger in our communities.
        </p>

        <div className="grid md:grid-cols-3 gap-6 mt-8">
          {[
            { icon: '🏪', title: 'For Donors', desc: 'Restaurants, stores, and individuals can donate excess food easily.', link: '/login/donor', btn: 'Get Started', color: 'from-green-400 to-green-600' },
            { icon: '🚚', title: 'For Volunteers', desc: 'Help transport food from donors to receivers in your area.', link: '/login/volunteer', btn: 'Join Us', color: 'from-blue-400 to-blue-600' },
            { icon: '🏠', title: 'For Receivers', desc: 'Food banks, shelters, and community organizations access food.', link: '/login/receiver', btn: 'Access Food', color: 'from-orange-400 to-orange-600' },
          ].map(({ icon, title, desc, link, btn, color }) => (
            <div key={title} className="bg-white bg-opacity-15 backdrop-blur-sm rounded-2xl p-8 card-hover border border-white border-opacity-20">
              <div className="text-5xl mb-4">{icon}</div>
              <h3 className="text-xl font-bold mb-3">{title}</h3>
              <p className="text-sm opacity-85 mb-6 leading-relaxed">{desc}</p>
              <Link to={link}
                className={`inline-block bg-gradient-to-r ${color} text-white px-6 py-2.5 rounded-full font-semibold hover:opacity-90 transition-opacity shadow-lg`}>
                {btn}
              </Link>
            </div>
          ))}
        </div>

        <div className="grid grid-cols-3 gap-8 mt-16 bg-white bg-opacity-10 rounded-2xl p-8">
          {[['500+', 'Donations Made'], ['200+', 'Volunteers Active'], ['50+', 'Organizations Helped']].map(([num, label]) => (
            <div key={label}>
              <div className="text-4xl font-bold">{num}</div>
              <div className="text-sm opacity-80 mt-1">{label}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
