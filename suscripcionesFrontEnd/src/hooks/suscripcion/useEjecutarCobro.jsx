import { useState } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { message } from 'antd'
import { api } from '../../services/api'

export const useEjecutarCobro = () => {
  const queryClient = useQueryClient()
  const [resumen, setResumen] = useState(null)

  const ejecutarMutation = useMutation({
    mutationFn: (resultado) =>
      api.post('/cobro/ejecutar', { resultado: resultado ?? null }),
    onSuccess: (data) => {
      setResumen(data)
      message.success('Motor de cobro ejecutado')
      queryClient.invalidateQueries({ queryKey: ['suscripciones-cliente'] })
      queryClient.invalidateQueries({ queryKey: ['suscripcion-detalle'] })
      queryClient.invalidateQueries({ queryKey: ['clientes'] })
    },
    onError: (e) => message.error(e.message),
  })

  const cerrarResumen = () => setResumen(null)

  return { ejecutarMutation, resumen, cerrarResumen }
}