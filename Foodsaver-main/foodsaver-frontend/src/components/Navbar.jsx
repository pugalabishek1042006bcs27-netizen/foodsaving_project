import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Navbar() {
  const { user, logout, isAuthenticated } = useAuth()
  const navigate = useNavigate()
  const [loginOpen, setLoginOpen] = useState(false)
  const [registerOpen, setRegisterOpen] = useState(false)
  const [mobileOpen, setMobileOpen] = useState(false)

  const handleLogout = () => { logout(); navigate('/') }

  const getDashboardLink = () => {
    if (!user) return '/'
    const links = {
      donor: '/donor/dashboard',
      volunteer: '/volunteer/dashboard',
      receiver: '/receiver/dashboard',
      admin: '/admin/dashboard',
    }
    return links[user.userType] || '/'
  }

  return (
    <nav className="bg-white shadow-lg fixed w-full top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <Link to="/" className="text-2xl font-bold text-purple-600">🍽️ FoodSavr</Link>

          {/* Desktop nav */}
          <div className="hidden md:flex items-center space-x-6">
            {[['/', 'Home'], ['/about', 'About'], ['/services', 'Services'], ['/contact', 'Contact']].map(([to, label]) => (
              <Link key={to} to={to} className="nav-link text-gray-700 hover:text-purple-600 font-medium">{label}</Link>
            ))}

            {isAuthenticated ? (
              <div className="flex items-center space-x-4">
                <Link to={getDashboardLink()} className="text-purple-600 font-semibold hover:text-purple-800">
                  👤 {user.name}
                </Link>
                <button onClick={handleLogout}
                  className="bg-red-500 text-white px-4 py-2 rounded-full hover:bg-red-600 transition-colors text-sm font-medium">
                  Logout
                </button>
              </div>
            ) : (
              <div className="flex items-center space-x-3">
                {/* Login dropdown */}
                <div className="relative">
                  <button onClick={() => { setLoginOpen(!loginOpen); setRegisterOpen(false) }}
                    className="bg-purple-600 text-white px-4 py-2 rounded-full hover:bg-purple-700 transition-colors text-sm font-medium">
                    👤 Login ▾
                  </button>
                  {loginOpen && (
                    <div className="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl py-2 border border-gray-100 z-50">
                      {[['donor','Donor','🏪'],['volunteer','Volunteer','🚚'],['receiver','Receiver','🏠'],['admin','Admin','🛡️']].map(([role,label,icon]) => (
                        <Link key={role} to={`/login/${role}`} onClick={() => setLoginOpen(false)}
                          className="flex items-center gap-2 px-4 py-2.5 text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors">
                          <span>{icon}</span><span>Login as {label}</span>
                        </Link>
                      ))}
                    </div>
                  )}
                </div>
                {/* Register dropdown */}
                <div className="relative">
                  <button onClick={() => { setRegisterOpen(!registerOpen); setLoginOpen(false) }}
                    className="bg-green-500 text-white px-4 py-2 rounded-full hover:bg-green-600 transition-colors text-sm font-medium">
                    ✍️ Register ▾
                  </button>
                  {registerOpen && (
                    <div className="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl py-2 border border-gray-100 z-50">
                      {[['donor','Donor','🏪'],['volunteer','Volunteer','🚚'],['receiver','Receiver','🏠']].map(([role,label,icon]) => (
                        <Link key={role} to={`/register/${role}`} onClick={() => setRegisterOpen(false)}
                          className="flex items-center gap-2 px-4 py-2.5 text-gray-700 hover:bg-green-50 hover:text-green-700 transition-colors">
                          <span>{icon}</span><span>Register as {label}</span>
                        </Link>
                      ))}
                    </div>
                  )}
                </div>
              </div>
            )}
          </div>

          {/* Mobile toggle */}
          <button className="md:hidden text-gray-700" onClick={() => setMobileOpen(!mobileOpen)}>
            <i className={`fas ${mobileOpen ? 'fa-times' : 'fa-bars'} text-xl`}></i>
          </button>
        </div>

        {/* Mobile menu */}
        {mobileOpen && (
          <div className="md:hidden pb-4 space-y-2 border-t border-gray-100 pt-3">
            {[['/', 'Home'], ['/about', 'About'], ['/services', 'Services'], ['/contact', 'Contact']].map(([to, label]) => (
              <Link key={to} to={to} onClick={() => setMobileOpen(false)}
                className="block py-2 text-gray-700 hover:text-purple-600 font-medium">{label}</Link>
            ))}
            {isAuthenticated ? (
              <>
                <Link to={getDashboardLink()} onClick={() => setMobileOpen(false)}
                  className="block py-2 text-purple-600 font-semibold">Dashboard</Link>
                <button onClick={handleLogout} className="block py-2 text-red-500 font-medium w-full text-left">Logout</button>
              </>
            ) : (
              <div className="space-y-1 pt-2 border-t border-gray-100">
                {[['donor','Donor'],['volunteer','Volunteer'],['receiver','Receiver'],['admin','Admin']].map(([role,label]) => (
                  <Link key={role} to={`/login/${role}`} onClick={() => setMobileOpen(false)}
                    className="block py-1.5 text-gray-600 hover:text-purple-600">Login as {label}</Link>
                ))}
                {[['donor','Donor'],['volunteer','Volunteer'],['receiver','Receiver']].map(([role,label]) => (
                  <Link key={role} to={`/register/${role}`} onClick={() => setMobileOpen(false)}
                    className="block py-1.5 text-gray-600 hover:text-green-600">Register as {label}</Link>
                ))}
              </div>
            )}
          </div>
        )}
      </div>
    </nav>
  )
}
