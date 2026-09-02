import { Tag } from 'antd'
import { STATE_COLORS, STATE_LABELS } from '../utils/estadoType';

export default function EstadoTag({ value }) {
  if (value === null || value === undefined) return null
  const color = STATE_COLORS[value] || 'default'
  return <Tag color={color}>{STATE_LABELS[value] || value}</Tag>
}
