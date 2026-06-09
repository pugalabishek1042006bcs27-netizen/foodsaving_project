import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { getAdminDashboard, getAllDonors, getAllVolunteers, getAllReceivers, getAllDonations, updateDonationStatus, assignVolunteer, getCertificates, updateCertificateStatus, getContactMessages } from '../../services/api'

const TABS = ['Overview', 'Donations', 'Users', 'Certificates', 'Messages']

export default function AdminDashboard() {
  const [tab, setTab] = useState('Overview')
  const [stats, setStats] = useState({})
  const [donors, setDonors] = useState([])
  const [volunteers, setVolunteers] = useState([])
  const [receivers, setReceivers] = useState([])
  const [donations, setDonations] = useState([])
  const [certificates, setCertificates] = useState([])
  const [messages, setMessages] = useState([])
  const [loading, setLoading] = useState(true)
  const [userTab, setUserTab] = useState('Donors')

  useEffect(() => {
    Promise.all([getAdminDashboard(), getAllDonors(), getAllVolunteers(), getAllReceivers(), getAllDonations(), getCertificates(), getContactMessages()])
      .then(([s, d, v, r, don, cert, msg]) => {
        setStats(s.data); setDonors(d.data); setVolunteers(v.data); setReceivers(r.data)
        setDonations(don.data); setCertificates(cert.data); setMessages(msg.data)
      }).catch(console.error).finally(() => setLoading(false))
  }, [])

  const handleStatusUpdate = async (id, status) => {
    await updateDonationStatus(id, status)
    setDonations(prev => prev.map(d => d.donationId === id ? { ...d, status } : d))
  }

  const handleAssign = async (donationId, volunteerId) => {
    if (!volunteerId) return
    try {
      await assignVolunteer(donationId, volunteerId)
      setDonations(prev => prev.map(d => d.donationId === donationId ? { ...d, volunteerId, status: 'accepted' } : d))
    } catch (err) {
      alert('Failed to assign: ' + (err.response?.data?.error || err.message))
    }
  }

  const handleCertUpdate = async (id, status) => {
    await updateCertificateStatus(id, status)
    setCertificates(prev => prev.map(c => c.certId === id ? { ...c, status } : c))
  }

  if (loading) return <div className="min-h-screen flex items-center justify-center"><div className="text-xl text-gray-500 animate-pulse">Loading...</div></div>

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-7xl mx-auto">
        <div className="bg-gradient-to-r from-red-600 to-red-800 text-white rounded-2xl p-8 mb-8 flex items-center justify-between flex-wrap gap-4">
          <div>
            <h1 className="text-3xl font-bold">🛡️ Admin Dashboard</h1>
            <p className="opacity-90 mt-1">Full system management and oversight</p>
          </div>
          <Link to="/tracking" className="bg-white text-red-700 px-5 py-2.5 rounded-xl font-semibold hover:bg-red-50 transition-colors shadow-md">📍 Live Tracking</Link>
        </div>

        <div className="flex gap-2 mb-6 flex-wrap">
          {TABS.map(t => (
            <button key={t} onClick={() => setTab(t)}
              className={`px-5 py-2.5 rounded-xl font-semibold text-sm transition-colors ${tab === t ? 'bg-red-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-red-50 shadow-sm'}`}>
              {t}
            </button>
          ))}
        </div>

        {tab === 'Overview' && (
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {[
              ['👤', 'Donors', stats.totalDonors, 'bg-purple-100 text-purple-700'],
              ['🚚', 'Volunteers', stats.totalVolunteers, 'bg-blue-100 text-blue-700'],
              ['🏠', 'Receivers', stats.totalReceivers, 'bg-orange-100 text-orange-700'],
              ['📦', 'Total Donations', stats.totalDonations, 'bg-green-100 text-green-700'],
              ['⏳', 'Pending', stats.pendingDonations, 'bg-yellow-100 text-yellow-700'],
              ['✅', 'Completed', stats.completedDonations, 'bg-teal-100 text-teal-700'],
              ['📜', 'Pending Certs', stats.pendingCertificates, 'bg-pink-100 text-pink-700'],
              ['💬', 'Messages', messages.length, 'bg-gray-100 text-gray-700'],
            ].map(([icon, label, value, color]) => (
              <div key={label} className={`${color} rounded-xl p-5 text-center shadow-sm`}>
                <div className="text-3xl mb-1">{icon}</div>
                <div className="text-3xl font-bold">{value ?? 0}</div>
                <div className="text-sm font-medium mt-1">{label}</div>
              </div>
            ))}
          </div>
        )}

        {tab === 'Donations' && (
          <div className="bg-white rounded-xl shadow-md p-6">
            <h2 className="text-lg font-bold text-gray-800 mb-4">All Donations ({donations.length})</h2>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead><tr className="bg-gray-50 text-gray-600">
                  {['ID', 'Food Type', 'Qty', 'Status', 'Update Status', 'Assign Volunteer', 'Date'].map(h => (
                    <th key={h} className="px-3 py-3 text-left font-semibold">{h}</th>
                  ))}
                </tr></thead>
                <tbody>
                  {donations.map(d => (
                    <tr key={d.donationId} className="border-t border-gray-100 hover:bg-gray-50">
                      <td className="px-3 py-3">#{d.donationId}</td>
                      <td className="px-3 py-3 font-medium">{d.foodType}</td>
                      <td className="px-3 py-3">{d.quantity}</td>
                      <td className="px-3 py-3">
                        <span className={`px-2 py-1 rounded-full text-xs font-semibold ${d.status === 'completed' ? 'bg-green-100 text-green-700' : d.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700'}`}>
                          {d.status}
                        </span>
                      </td>
                      <td className="px-3 py-3">
                        <select value={d.status} onChange={e => handleStatusUpdate(d.donationId, e.target.value)}
                          className="border border-gray-300 rounded px-2 py-1 text-xs">
                          {['pending', 'accepted', 'in_progress', 'completed', 'cancelled'].map(s => <option key={s} value={s}>{s}</option>)}
                        </select>
                      </td>
                      <td className="px-3 py-3">
                        <select value={d.volunteerId || ''} onChange={e => handleAssign(d.donationId, e.target.value)}
                          className="border border-gray-300 rounded px-2 py-1 text-xs">
                          <option value="">Assign...</option>
                          {volunteers.map(v => <option key={v.volunteerId} value={v.volunteerId}>{v.name}</option>)}
                        </select>
                      </td>
                      <td className="px-3 py-3 text-gray-500">{new Date(d.uploadDate).toLocaleDateString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {tab === 'Users' && (
          <div className="bg-white rounded-xl shadow-md p-6">
            <div className="flex gap-2 mb-6">
              {['Donors', 'Volunteers', 'Receivers'].map(t => (
                <button key={t} onClick={() => setUserTab(t)}
                  className={`px-4 py-2 rounded-lg text-sm font-semibold transition-colors ${userTab === t ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}>{t}</button>
              ))}
            </div>
            {userTab === 'Donors' && (
              <table className="w-full text-sm">
                <thead><tr className="bg-gray-50">{['ID','Name','Email','Contact','Joined'].map(h => <th key={h} className="px-4 py-3 text-left font-semibold text-gray-600">{h}</th>)}</tr></thead>
                <tbody>{donors.map(d => <tr key={d.donorId} className="border-t hover:bg-gray-50"><td className="px-4 py-3">#{d.donorId}</td><td className="px-4 py-3 font-medium">{d.name}</td><td className="px-4 py-3">{d.email}</td><td className="px-4 py-3">{d.contactNumber}</td><td className="px-4 py-3 text-gray-500">{new Date(d.createdAt).toLocaleDateString()}</td></tr>)}</tbody>
              </table>
            )}
            {userTab === 'Volunteers' && (
              <table className="w-full text-sm">
                <thead><tr className="bg-gray-50">{['ID','Name','Email','Region','Availability'].map(h => <th key={h} className="px-4 py-3 text-left font-semibold text-gray-600">{h}</th>)}</tr></thead>
                <tbody>{volunteers.map(v => <tr key={v.volunteerId} className="border-t hover:bg-gray-50"><td className="px-4 py-3">#{v.volunteerId}</td><td className="px-4 py-3 font-medium">{v.name}</td><td className="px-4 py-3">{v.email}</td><td className="px-4 py-3">{v.region}</td><td className="px-4 py-3">{v.availability}</td></tr>)}</tbody>
              </table>
            )}
            {userTab === 'Receivers' && (
              <table className="w-full text-sm">
                <thead><tr className="bg-gray-50">{['ID','Organization','Contact','Email','City'].map(h => <th key={h} className="px-4 py-3 text-left font-semibold text-gray-600">{h}</th>)}</tr></thead>
                <tbody>{receivers.map(r => <tr key={r.receiverId} className="border-t hover:bg-gray-50"><td className="px-4 py-3">#{r.receiverId}</td><td className="px-4 py-3 font-medium">{r.orgName}</td><td className="px-4 py-3">{r.receiverName}</td><td className="px-4 py-3">{r.email}</td><td className="px-4 py-3">{r.city}</td></tr>)}</tbody>
              </table>
            )}
          </div>
        )}

        {tab === 'Certificates' && (
          <div className="bg-white rounded-xl shadow-md p-6">
            <h2 className="text-lg font-bold text-gray-800 mb-4">Certificates ({certificates.length})</h2>
            <table className="w-full text-sm">
              <thead><tr className="bg-gray-50">{['ID','Receiver ID','File','Status','Actions','Date'].map(h => <th key={h} className="px-4 py-3 text-left font-semibold text-gray-600">{h}</th>)}</tr></thead>
              <tbody>
                {certificates.map(c => (
                  <tr key={c.certId} className="border-t hover:bg-gray-50">
                    <td className="px-4 py-3">#{c.certId}</td>
                    <td className="px-4 py-3">#{c.receiverId}</td>
                    <td className="px-4 py-3 text-blue-600"><a href={`${import.meta.env.VITE_API_URL}/${c.filePath}`} target="_blank" rel="noreferrer" className="hover:underline">View File</a></td>
                    <td className="px-4 py-3"><span className={`px-2 py-1 rounded-full text-xs font-semibold ${c.status === 'Approved' ? 'bg-green-100 text-green-700' : c.status === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'}`}>{c.status}</span></td>
                    <td className="px-4 py-3">
                      <div className="flex gap-2">
                        <button onClick={() => handleCertUpdate(c.certId, 'Approved')} className="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600">Approve</button>
                        <button onClick={() => handleCertUpdate(c.certId, 'Rejected')} className="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600">Reject</button>
                      </div>
                    </td>
                    <td className="px-4 py-3 text-gray-500">{new Date(c.uploadedAt).toLocaleDateString()}</td>
                  </tr>
                ))}
                {certificates.length === 0 && <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">No certificates yet.</td></tr>}
              </tbody>
            </table>
          </div>
        )}

        {tab === 'Messages' && (
          <div className="bg-white rounded-xl shadow-md p-6">
            <h2 className="text-lg font-bold text-gray-800 mb-4">Contact Messages ({messages.length})</h2>
            <div className="space-y-4">
              {messages.map(m => (
                <div key={m.id} className="border border-gray-200 rounded-xl p-4 hover:bg-gray-50">
                  <div className="flex justify-between items-start mb-2">
                    <div>
                      <span className="font-semibold text-gray-800">{m.name}</span>
                      <span className="text-gray-500 text-sm ml-2">— {m.email}</span>
                    </div>
                    <span className="text-xs text-gray-400">{new Date(m.createdAt).toLocaleString()}</span>
                  </div>
                  <p className="text-gray-600 text-sm">{m.message}</p>
                </div>
              ))}
              {messages.length === 0 && <p className="text-center text-gray-400 py-8">No messages yet.</p>}
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
