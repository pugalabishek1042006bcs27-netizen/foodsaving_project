import api from './api'

function loadRazorpayScript() {
  return new Promise((resolve, reject) => {
    if (window.Razorpay) return resolve(true)
    const script = document.createElement('script')
    script.src = 'https://checkout.razorpay.com/v1/checkout.js'
    script.onload = () => resolve(true)
    script.onerror = () => reject(new Error('Failed to load Razorpay SDK'))
    document.body.appendChild(script)
  })
}

export async function initiatePayment({ amount, currency = 'INR', donationId, notes = {} }) {
  await loadRazorpayScript()

  const { data: order } = await api.post('/api/payment/create-order', {
    amount, currency, donationId, notes,
  })

  return new Promise((resolve, reject) => {
    const options = {
      key: order.keyId,
      amount: order.amount,
      currency: order.currency,
      order_id: order.orderId,
      name: 'FoodSaver',
      description: `Donation Payment #${donationId}`,
      prefill: { contact: '', email: '' },
      notes,
      handler: async function (response) {
        try {
          const { data: verification } = await api.post('/api/payment/verify', {
            razorpayOrderId: response.razorpay_order_id,
            razorpayPaymentId: response.razorpay_payment_id,
            razorpaySignature: response.razorpay_signature,
          })
          resolve(verification)
        } catch (err) {
          reject(err)
        }
      },
      modal: { ondismiss: () => reject(new Error('Payment cancelled')) },
    }

    const rzp = new window.Razorpay(options)
    rzp.open()
  })
}
