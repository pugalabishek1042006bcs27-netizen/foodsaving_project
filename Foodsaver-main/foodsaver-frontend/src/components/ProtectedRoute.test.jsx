import { describe, it, expect } from 'vitest'
import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import ProtectedRoute from './ProtectedRoute'
import { AuthProvider } from '../context/AuthContext'

describe('ProtectedRoute', () => {
  beforeEach(() => localStorage.clear())

  it('renders children when authenticated', () => {
    localStorage.setItem('token', 'test-token')
    localStorage.setItem('foodsaver_user', JSON.stringify({ userType: 'donor', name: 'Test' }))

    render(
      <MemoryRouter>
        <AuthProvider>
          <ProtectedRoute><div>Protected Content</div></ProtectedRoute>
        </AuthProvider>
      </MemoryRouter>
    )

    expect(screen.getByText('Protected Content')).toBeTruthy()
  })

  it('redirects when not authenticated', () => {
    render(
      <MemoryRouter initialEntries={['/']}>
        <AuthProvider>
          <ProtectedRoute><div>Protected Content</div></ProtectedRoute>
        </AuthProvider>
      </MemoryRouter>
    )

    expect(screen.queryByText('Protected Content')).toBeFalsy()
  })

  it('redirects when role does not match', () => {
    localStorage.setItem('token', 'test-token')
    localStorage.setItem('foodsaver_user', JSON.stringify({ userType: 'donor', name: 'Test' }))

    render(
      <MemoryRouter>
        <AuthProvider>
          <ProtectedRoute requiredRole="admin"><div>Admin Only</div></ProtectedRoute>
        </AuthProvider>
      </MemoryRouter>
    )

    expect(screen.queryByText('Admin Only')).toBeFalsy()
  })
})
