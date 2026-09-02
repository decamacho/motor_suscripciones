import { Select } from 'antd'

const OPCIONES = [
  { value: undefined, label: 'Automático (aleatorio)' },
  { value: 'aprobado', label: 'Aprobado' },
  { value: 'rechazado', label: 'Rechazado' },
  { value: 'timeout', label: 'Timeout' },
]

export default function ResultadoCobroSelect({ value, onChange, ...rest }) {
  return (
    <Select
      {...rest}
      value={value ?? undefined}
      onChange={onChange}
      placeholder="Automático (aleatorio)"
      options={OPCIONES}
      allowClear
    />
  )
}