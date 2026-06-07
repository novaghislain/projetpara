'use client';

import React, { useState, useEffect, Suspense } from 'react';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { motion, AnimatePresence } from 'framer-motion';
import { ShieldCheck, CreditCard, CheckCircle2, MapPin } from 'lucide-react';
import { useSearchParams } from 'next/navigation';

function CheckoutContent() {
  const searchParams = useSearchParams();
  const [products, setProducts] = useState([]);
  const [step, setStep] = useState(1);
  const [payMethod, setPayMethod] = useState('');
  const [item, setItem] = useState(null);
  const [loading, setLoading] = useState(true);
  const [orderId, setOrderId] = useState('');

  // Customer Info State
  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    phone: '',
    city: 'Cotonou',
    address: ''
  });

  useEffect(() => {
    fetch('/api/products')
      .then(res => res.json())
      .then(data => {
        setProducts(data);
        const id = searchParams.get('id');
        // Handle numeric IDs or the old string tags for 1 and 3
        const found = data.find(p =>
          p.id.toString() === id ||
          (id === 'serum' && p.id === 1) ||
          (id === 'bronze' && p.id === 3)
        );
        setItem(found || data[0]);
        setLoading(false);
      })
      .catch(err => {
        console.error('Failed to fetch products:', err);
        setLoading(false);
      });
  }, [searchParams]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleNext = async () => {
    if (step === 2) {
      // Finalize Order
      const order = {
        customer: formData,
        paymentMethod: payMethod,
        items: [{
          id: item.id,
          name: item.name,
          price: item.price,
          quantity: 1
        }],
        total: item.price
      };

      try {
        const res = await fetch('/api/orders', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(order)
        });
        const data = await res.json();
        setOrderId(data.id);
        setStep(step + 1);
      } catch (err) {
        alert('Erreur lors de la commande. Veuillez réessayer.');
      }
    } else {
      setStep(step + 1);
    }
  };

  if (loading) return <div className="text-center py-5 mt-5">Chargement de votre panier...</div>;
  if (!item) return <div className="text-center py-5 mt-5">Produit introuvable.</div>;

  return (
    <div className="container py-5 mt-5">
        <div className="max-w-4xl mx-auto">
          {/* Progress Tracker */}
          <div className="d-flex align-items-center justify-content-between mb-5 position-relative">
            <div className="position-absolute top-50 start-0 w-100 bg-secondary bg-opacity-20 translate-middle-y" style={{height: '2px', zIndex: 0}}></div>
            <div className="position-absolute top-50 start-0 bg-primary translate-middle-y transition-all" style={{height: '2px', width: `${(step - 1) * 50}%`, zIndex: 0, transition: 'width 0.5s ease'}}></div>

            {[
              { id: 1, label: 'Livraison', icon: MapPin },
              { id: 2, label: 'Paiement', icon: CreditCard },
              { id: 3, label: 'Confirmation', icon: CheckCircle2 }
            ].map(s => (
              <div key={s.id} className="d-flex flex-column align-items-center gap-2 position-relative" style={{zIndex: 1}}>
                <div className={`rounded-circle d-flex align-items-center justify-content-center transition-all ${
                  step >= s.id ? 'bg-primary text-white shadow' : 'bg-white border border-2 text-muted'
                }`} style={{width: '45px', height: '45px'}}>
                  <s.icon size={20} />
                </div>
                <span className={`small fw-bold text-uppercase ls-1 ${step >= s.id ? 'text-primary' : 'text-muted'}`} style={{fontSize: '10px'}}>{s.label}</span>
              </div>
            ))}
          </div>

          <div className="row g-4">
            {/* Main Content */}
            <div className="col-lg-8">
              <AnimatePresence mode="wait">
                {step === 1 && (
                  <motion.div
                    key="step1"
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: 20 }}
                    className="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white"
                  >
                    <h2 className="h3 fw-bold mb-4 text-dark">Informations de livraison</h2>
                    <div className="row g-3 mb-4">
                      <div className="col-md-6">
                        <label className="x-small fw-bold text-muted text-uppercase mb-2 d-block">Nom</label>
                        <input type="text" name="lastName" value={formData.lastName} onChange={handleInputChange} className="form-control rounded-3 border-0 bg-light py-3 px-4" placeholder="Votre nom" required />
                      </div>
                      <div className="col-md-6">
                        <label className="x-small fw-bold text-muted text-uppercase mb-2 d-block">Prénom</label>
                        <input type="text" name="firstName" value={formData.firstName} onChange={handleInputChange} className="form-control rounded-3 border-0 bg-light py-3 px-4" placeholder="Votre prénom" required />
                      </div>
                    </div>
                    <div className="mb-4">
                      <label className="x-small fw-bold text-muted text-uppercase mb-2 d-block">Téléphone Mobile Money</label>
                      <input type="tel" name="phone" value={formData.phone} onChange={handleInputChange} className="form-control rounded-3 border-0 bg-light py-3 px-4" placeholder="+229 00 00 00 00" required />
                    </div>
                    <div className="row g-3 mb-5">
                      <div className="col-md-6">
                        <label className="x-small fw-bold text-muted text-uppercase mb-2 d-block">Ville</label>
                        <select name="city" value={formData.city} onChange={handleInputChange} className="form-select rounded-3 border-0 bg-light py-3 px-4">
                          <option>Cotonou</option>
                          <option>Abomey-Calavi</option>
                          <option>Porto-Novo</option>
                          <option>Parakou</option>
                        </select>
                      </div>
                      <div className="col-md-6">
                        <label className="x-small fw-bold text-muted text-uppercase mb-2 d-block">Quartier / Adresse</label>
                        <input type="text" name="address" value={formData.address} onChange={handleInputChange} className="form-control rounded-3 border-0 bg-light py-3 px-4" placeholder="Fidjrossè, Cadjehoun..." required />
                      </div>
                    </div>
                    <button
                      onClick={handleNext}
                      disabled={!formData.firstName || !formData.lastName || !formData.phone}
                      className={`btn w-100 py-3 rounded-3 shadow-lg fw-bold ${(!formData.firstName || !formData.lastName || !formData.phone) ? 'btn-light text-muted' : 'btn-primary'}`}
                    >
                      Continuer vers le paiement
                    </button>
                  </motion.div>
                )}

                {step === 2 && (
                  <motion.div
                    key="step2"
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: 20 }}
                    className="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white"
                  >
                    <h2 className="h3 fw-bold mb-4 text-dark">Moyen de paiement</h2>
                    <div className="d-grid gap-3 mb-5">
                      {[
                        { id: 'momo', label: 'MTN Mobile Money', color: 'bg-warning' },
                        { id: 'moov', label: 'Moov Money', color: 'bg-primary' },
                        { id: 'cod', label: 'Paiement à la livraison', color: 'bg-secondary' }
                      ].map(method => (
                        <div
                          key={method.id}
                          onClick={() => setPayMethod(method.id)}
                          className={`card border-2 cursor-pointer transition-all p-3 rounded-4 ${
                            payMethod === method.id ? 'border-primary bg-primary bg-opacity-5' : 'border-light'
                          }`}
                        >
                          <div className="d-flex align-items-center justify-content-between">
                            <div className="d-flex align-items-center gap-3">
                              <div className={`${method.color} rounded-circle`} style={{width: '40px', height: '40px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'white', fontWeight: 'bold', fontSize: '10px'}}>
                                {method.id.toUpperCase()}
                              </div>
                              <span className="fw-bold">{method.label}</span>
                            </div>
                            <div className={`rounded-circle border border-2 d-flex align-items-center justify-content-center ${
                              payMethod === method.id ? 'border-primary bg-primary' : 'border-light bg-white'
                            }`} style={{width: '20px', height: '20px'}}>
                              {payMethod === method.id && <div className="bg-white rounded-circle" style={{width: '8px', height: '8px'}}></div>}
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>

                    <button
                      disabled={!payMethod}
                      onClick={handleNext}
                      className={`btn w-100 py-3 rounded-3 fw-bold ${payMethod ? 'btn-primary shadow-lg' : 'btn-light text-muted cursor-not-allowed'}`}
                    >
                      Confirmer la commande
                    </button>
                  </motion.div>
                )}

                {step === 3 && (
                  <motion.div
                    key="step3"
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    className="card border-0 shadow-sm rounded-4 p-5 text-center bg-white"
                  >
                    <div className="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style={{width: '80px', height: '80px'}}>
                      <CheckCircle2 size={40} />
                    </div>
                    <h2 className="h2 fw-bold mb-3 text-dark">Commande Réussie !</h2>
                    <p className="text-muted mb-5 px-md-5">
                      Merci pour votre confiance. Votre commande <span className="text-primary fw-bold">#VP-{orderId}</span> a été enregistrée. Un agent vous contactera par téléphone sous peu.
                    </p>
                    <a href="/" className="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-lg mt-2 mb-2 d-inline-block">Retour à l'accueil</a>
                  </motion.div>
                )}
              </AnimatePresence>
            </div>

            {/* Sidebar Summary */}
            <div className="col-lg-4">
              <div className="card border-0 shadow-sm rounded-4 p-4 sticky-top bg-white" style={{top: '100px'}}>
                <h5 className="fw-bold mb-4">Votre Résumé</h5>
                <div className="d-flex align-items-center gap-3 mb-4">
                  <img src={item.img} className="rounded-3" style={{width: '60px', height: '60px', objectFit: 'cover'}} />
                  <div className="flex-grow-1">
                    <h6 className="small fw-bold mb-0">{item.name}</h6>
                    <small className="text-muted">1x {item.price.toLocaleString()} FCFA</small>
                  </div>
                </div>
                <hr className="my-4 text-muted opacity-20" />
                <div className="d-flex justify-content-between mb-2">
                  <span className="text-muted small">Sous-total</span>
                  <span className="fw-bold small">{item.price.toLocaleString()} FCFA</span>
                </div>
                <div className="d-flex justify-content-between mb-4">
                  <span className="text-muted small">Frais de traitement</span>
                  <span className="text-success small fw-bold">Gratuit</span>
                </div>
                <div className="d-flex justify-content-between h4 fw-bold text-primary border-top pt-4">
                  <span>Total</span>
                  <span>{item.price.toLocaleString()} FCFA</span>
                </div>
                <div className="mt-4 p-3 bg-light rounded-3 d-flex align-items-center gap-2">
                  <ShieldCheck size={16} className="text-primary" />
                  <span className="x-small fw-bold text-muted uppercase" style={{fontSize: '9px'}}>Paiement 100% Sécurisé</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <style jsx>{`
            .ls-1 { letter-spacing: 1px; }
            .x-small { font-size: 0.75rem; }
            .transition-all { transition: all 0.3s ease !important; }
        `}</style>
    </div>
  );
}

export default function CheckoutPage() {
  return (
    <div className="min-h-screen bg-light">
      <Header />
      <Suspense fallback={<div className="container py-5 mt-5 text-center">Chargement du panier...</div>}>
         <CheckoutContent />
      </Suspense>
      <Footer />
    </div>
  );
}
