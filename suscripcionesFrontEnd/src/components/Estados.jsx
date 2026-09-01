import { Alert, Empty, Spin } from 'antd'

export function LoadingWrapper() {
  return (
    <div className="flex items-center justify-center py-16">
      <Spin size="large" />
    </div>
  )
}

export function ErrorAlert({ error }) {
  if (!error) return null
  return (
    <Alert
      type="error"
      showIcon
      title="Ocurrió un error"
      description={error?.message || 'No se pudo completar la operación.'}
    />
  )
}

export function EmptyState({ description = 'No hay registros todavía.' }) {
  return (
    <div className="py-10">
      <Empty description={description} />
    </div>
  )
}
