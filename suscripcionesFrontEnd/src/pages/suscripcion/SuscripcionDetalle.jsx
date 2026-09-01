import {
  Button,
  Card,
  Descriptions,
  Table,
  Tag,
} from 'antd'
import { useQuery } from '@tanstack/react-query'
import { useNavigate, useParams } from 'react-router-dom'
import { api } from '../../services/api'
import EstadoTag from '../../components/EstadoTag'
import { ErrorAlert, EmptyState, LoadingWrapper } from '../../components/Estados'
import { formatoFecha } from '../../utils/formatoFecha'

export default function SuscripcionDetalle() {
  const { id } = useParams()
  const navigate = useNavigate()

  const { data: susc, isLoading, isError, error } = useQuery({
    queryKey: ['suscripcion-detalle', id],
    queryFn: () => api.get(`/cliente-suscripciones/${id}`),
  })

  const { data: cobros, isFetching: cobrosFetching, isError: cobrosError } = useQuery({
    queryKey: ['suscripcion-cobros', id],
    queryFn: () => api.get(`/cliente-suscripciones/${id}/cobros`),
    enabled: !!id,
  })

  const cobroColumns = [
    {
      title: 'Intento',
      dataIndex: 'cobro_intento_numero',
      width: 100,
      render: (v) => <Tag>{v}</Tag>,
    },
    {
      title: 'Monto',
      dataIndex: 'cobro_monto',
      render: (v) => `${v} $`,
    },
    {
      title: 'Estado del cobro',
      dataIndex: 'cobro_estado',
      render: (v) => <EstadoTag value={v} />,
    },
    {
      title: 'Resultado pasarela',
      dataIndex: 'cobro_resultado_pasarela',
      render: (v) => (v ? <EstadoTag value={v} /> : '—'),
    },
    {
      title: 'Fecha',
      dataIndex: 'cobro_fecha',
      render: (v) => formatoFecha(v),
    },
  ]

  if (isLoading) return <LoadingWrapper />

  if (isError) {
    return (
      <div className="space-y-4">
        <ErrorAlert error={error} />
        <Button onClick={() => navigate('/suscripciones')}>
          ← Volver
        </Button>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <Button onClick={() => navigate('/suscripciones')}>← Volver</Button>

      <Card title="Detalle de la suscripción">
        <Descriptions bordered column={{ xs: 1, sm: 2, md: 3 }}>
          <Descriptions.Item label="Cliente">
            {susc.cliente_nombre}
          </Descriptions.Item>
          <Descriptions.Item label="Plan">
            {susc.suscripcion_nombre}
          </Descriptions.Item>
          <Descriptions.Item label="Periodo">
            <EstadoTag value={susc.suscripcion_periodo} />
          </Descriptions.Item>
          <Descriptions.Item label="Precio">
            {susc.suscripcion_precio} $
          </Descriptions.Item>
          <Descriptions.Item label="Estado">
            <EstadoTag value={susc.estado_cliente_suscripcion} />
          </Descriptions.Item>
          <Descriptions.Item label="Próximo cobro">
            {formatoFecha(susc.fecha_proximo_cobro)}
          </Descriptions.Item>
          <Descriptions.Item label="Último cobro" span={3}>
            {formatoFecha(susc.fecha_ultimo_cobro)}
          </Descriptions.Item>
        </Descriptions>
      </Card>

      <Card title="Historial de intentos de cobro">
        {cobrosError ? (
          <ErrorAlert error={cobrosError} />
        ) : (!cobros || cobros.length === 0) && !cobrosFetching ? (
          <EmptyState description="Todavía no hay intentos de cobro para esta suscripción." />
        ) : (
          <Table
            rowKey="cobro_suscripcion_id"
            columns={cobroColumns}
            dataSource={cobros || []}
            pagination={false}
            loading={cobrosFetching}
          />
        )}
      </Card>
    </div>
  )
}
