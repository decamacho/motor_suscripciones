import { Routes, Route, Navigate } from 'react-router-dom'
import AppLayout from './components/Layout'
import Clientes from './pages/cliente/Clientes'
import ClienteDetalle from './pages/cliente/ClienteDetalle'
import Suscripcion from './pages/suscripcion/Suscripcion'
import Suscripciones from './pages/suscripcion/Suscripciones'
import SuscripcionDetalle from './pages/suscripcion/SuscripcionDetalle'

function App() {
  return (
    <Routes>
      <Route element={<AppLayout />}>
        <Route path="/" element={<Clientes />} />
        <Route path="/clientes/:clienteId" element={<ClienteDetalle />} />
        <Route path="/suscripcion" element={<Suscripcion />} />
        <Route path="/suscripciones" element={<Suscripciones />} />
        <Route path="/suscripciones/:id" element={<SuscripcionDetalle />} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Route>
    </Routes>
  )
}

export default App
