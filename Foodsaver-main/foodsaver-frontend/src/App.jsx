import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { AuthProvider } from './context/AuthContext'
import Navbar from './components/Navbar'
import Footer from './components/Footer'
import ProtectedRoute from './components/ProtectedRoute'

import Home from './pages/Home'
import About from './pages/About'
import Services from './pages/Services'
import Contact from './pages/Contact'

import DonorLogin from './pages/auth/DonorLogin'
import DonorRegister from './pages/auth/DonorRegister'
import VolunteerLogin from './pages/auth/VolunteerLogin'
import VolunteerRegister from './pages/auth/VolunteerRegister'
import ReceiverLogin from './pages/auth/ReceiverLogin'
import ReceiverRegister from './pages/auth/ReceiverRegister'
import AdminLogin from './pages/auth/AdminLogin'

import DonorDashboard from './pages/donor/DonorDashboard'
import UploadDonation from './pages/donor/UploadDonation'
import VolunteerDashboard from './pages/volunteer/VolunteerDashboard'
import VolunteerActivity from './pages/volunteer/VolunteerActivity'
import LiveTracking from './pages/volunteer/LiveTracking'
import ReceiverDashboard from './pages/receiver/ReceiverDashboard'
import BrowseDonations from './pages/receiver/BrowseDonations'
import AdminDashboard from './pages/admin/AdminDashboard'

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <div className="flex flex-col min-h-screen">
          <Navbar />
          <main className="flex-1 pt-16">
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/about" element={<About />} />
              <Route path="/services" element={<Services />} />
              <Route path="/contact" element={<Contact />} />

              <Route path="/login/donor" element={<DonorLogin />} />
              <Route path="/login/volunteer" element={<VolunteerLogin />} />
              <Route path="/login/receiver" element={<ReceiverLogin />} />
              <Route path="/login/admin" element={<AdminLogin />} />
              <Route path="/register/donor" element={<DonorRegister />} />
              <Route path="/register/volunteer" element={<VolunteerRegister />} />
              <Route path="/register/receiver" element={<ReceiverRegister />} />

              <Route path="/donor/dashboard" element={
                <ProtectedRoute requiredRole="donor"><DonorDashboard /></ProtectedRoute>
              } />
              <Route path="/donor/upload" element={
                <ProtectedRoute requiredRole="donor"><UploadDonation /></ProtectedRoute>
              } />
              <Route path="/volunteer/dashboard" element={
                <ProtectedRoute requiredRole="volunteer"><VolunteerDashboard /></ProtectedRoute>
              } />
              <Route path="/volunteer/activity" element={
                <ProtectedRoute requiredRole="volunteer"><VolunteerActivity /></ProtectedRoute>
              } />
              <Route path="/volunteer/tracking" element={
                <ProtectedRoute requiredRole="volunteer"><LiveTracking /></ProtectedRoute>
              } />
              <Route path="/tracking" element={
                <ProtectedRoute><LiveTracking /></ProtectedRoute>
              } />
              <Route path="/receiver/dashboard" element={
                <ProtectedRoute requiredRole="receiver"><ReceiverDashboard /></ProtectedRoute>
              } />
              <Route path="/receiver/browse" element={
                <ProtectedRoute requiredRole="receiver"><BrowseDonations /></ProtectedRoute>
              } />
              <Route path="/admin/dashboard" element={
                <ProtectedRoute requiredRole="admin"><AdminDashboard /></ProtectedRoute>
              } />
            </Routes>
          </main>
          <Footer />
        </div>
      </AuthProvider>
    </BrowserRouter>
  )
}
