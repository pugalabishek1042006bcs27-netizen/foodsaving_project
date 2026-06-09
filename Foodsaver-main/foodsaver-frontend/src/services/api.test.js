import { describe, it, expect } from 'vitest'
import api, { loginDonor, loginVolunteer, loginReceiver, loginAdmin, registerDonor, registerVolunteer, registerReceiver } from './api'

describe('API service', () => {
  it('creates axios instance with correct baseURL', () => {
    expect(api.defaults.baseURL).toBe(import.meta.env.VITE_API_URL)
  })

  it('exports auth login functions', () => {
    expect(typeof loginDonor).toBe('function')
    expect(typeof loginVolunteer).toBe('function')
    expect(typeof loginReceiver).toBe('function')
    expect(typeof loginAdmin).toBe('function')
  })

  it('exports registration functions', () => {
    expect(typeof registerDonor).toBe('function')
    expect(typeof registerVolunteer).toBe('function')
    expect(typeof registerReceiver).toBe('function')
  })
})
