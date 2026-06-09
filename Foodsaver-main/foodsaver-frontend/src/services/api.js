import axios from 'axios'

const api = axios.create({ baseURL: import.meta.env.VITE_API_URL })

api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Auth
export const loginDonor = d => api.post('/api/auth/login/donor', d)
export const loginVolunteer = d => api.post('/api/auth/login/volunteer', d)
export const loginReceiver = d => api.post('/api/auth/login/receiver', d)
export const loginAdmin = d => api.post('/api/auth/login/admin', d)
export const registerDonor = d => api.post('/api/auth/register/donor', d)
export const registerVolunteer = d => api.post('/api/auth/register/volunteer', d)
export const registerReceiver = d => api.post('/api/auth/register/receiver', d)

// Donor
export const getDonorProfile = () => api.get('/api/donor/profile')
export const getDonorDonations = () => api.get('/api/donor/donations')
export const uploadDonation = formData =>
  api.post('/api/donations/upload', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })

// Volunteer
export const getVolunteerProfile = () => api.get('/api/volunteer/profile')
export const getVolunteerAvailableDonations = () => api.get('/api/volunteer/available-donations')
export const acceptDonation = id => api.post(`/api/volunteer/accept/${id}`)
export const getMyDeliveries = () => api.get('/api/volunteer/my-deliveries')
export const verifyPickup = data => api.post('/api/donations/verify-pickup', data)

// Receiver
export const getReceiverProfile = () => api.get('/api/receiver/profile')
export const getAvailableDonations = () => api.get('/api/donations/available')
export const requestDonation = (id, data) => api.post(`/api/receiver/request/${id}`, data)
export const getMyRequests = () => api.get('/api/receiver/my-requests')
export const verifyDelivery = data => api.post('/api/donations/verify-delivery', data)

// Admin
export const getAdminDashboard = () => api.get('/api/admin/dashboard')
export const getAllDonors = () => api.get('/api/admin/donors')
export const getAllVolunteers = () => api.get('/api/admin/volunteers')
export const getAllReceivers = () => api.get('/api/admin/receivers')
export const getAllDonations = () => api.get('/api/admin/donations')
export const updateDonationStatus = (id, status) =>
  api.put(`/api/admin/donations/${id}/status`, { status })
export const assignVolunteer = (donationId, volunteerId) =>
  api.post(`/api/admin/accept-donation/${donationId}/${volunteerId}`)
export const getCertificates = () => api.get('/api/admin/certificates')
export const updateCertificateStatus = (id, status) =>
  api.put(`/api/admin/certificates/${id}/status`, { status })
export const getContactMessages = () => api.get('/api/admin/contact-messages')

// Notifications
export const getNotifications = (userType, userId) =>
  api.get(`/api/notifications/${userType}/${userId}`)

// Contact
export const submitContact = data => api.post('/api/contact/submit', data)

// Payment
export const createPaymentOrder = data => api.post('/api/payment/create-order', data)
export const verifyPayment = data => api.post('/api/payment/verify', data)

export default api
