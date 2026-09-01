import { useMemo, useState } from 'react'
import { Button, Card, Select, Space, Table } from 'antd'
import { PlayCircleOutlined } from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { api } from '../../services/api'
import EstadoTag from '../../components/EstadoTag'
import ResultadoCobroModal from '../../components/ResultadoCobroModal'
import ResultadoCobroSelect from '../../components/ResultadoCobroSelect'
import { ErrorAlert, EmptyState } from '../../components/Estados'
import { useEjecutarCobro } from '../../hooks/suscripcion/useEjecutarCobro'
import { formatoFecha } from '../../utils/formatoFecha'

export default function Suscripciones() {
  const navigate = useNavigate()
  const [filtro, setFiltro] = useState(null)
  const [resultado, setResultado] = useState(undefined)
  const { ejecutarMutation, resumen, cerrarResumen } = useEjecutarCobro()

  const { data, isFetching, isError, error } = useQuery({
    queryKey: ['suscripciones-cliente'],
    queryFn: () => api.get('/cliente-suscripciones'),
  })

  const filtered = useMemo(() => {
    if (!filtro || !data) return data || []
    return data.filter((s) => s.estado_cliente_suscripcion === filtro)
  }, [data, filtro])

  const columns = [
    { title: 'Cliente', dataIndex: 'cliente_nombre' },
    {
      title: 'Plan',
      dataIndex: 'suscripcion_nombre',
      render: (v, r) => (
        <span>
          {v}{' '}
          {r.suscripcion_periodo && <EstadoTag value={r.suscripcion_periodo} />}
        </span>
      ),
    },
    {
      title: 'Precio',
      dataIndex: 'suscripcion_precio',
      render: (v) => `${v} $`,
    },
    {
      title: 'Estado',
      dataIndex: 'estado_cliente_suscripcion',
      render: (v) => <EstadoTag value={v} />,
    },
    {
      title: 'Próximo cobro',
      dataIndex: 'fecha_proximo_cobro',
      render: (v) => formatoFecha(v),
    },
    {
      title: 'Acciones',
      key: 'acciones',
      width: 140,
      render: (_, record) => (
        <Button
          type="link"
          onClick={() => navigate(`/suscripciones/${record.cliente_suscripcion_id}`, {
            state: { clienteId: record.cliente_id },
          })}
        >
          Detalle cobro
        </Button>
      ),
    },
  ]

  return (
    <div className="space-y-4">
      <Space style={{ display: 'flex', justifyContent: 'space-between' }}>
        <Space>
          <ResultadoCobroSelect
            value={resultado}
            onChange={setResultado}
            style={{ width: 220 }}
            placeholder="Automático (aleatorio)"
          />
          <Button
            type="primary"
            icon={<PlayCircleOutlined />}
            loading={ejecutarMutation.isPending}
            onClick={() => ejecutarMutation.mutate(resultado)}
          >
            Ejecutar cobro
          </Button>
        </Space>
      </Space>

      <Card
        title="Suscripciones de clientes"
        extra={
          <Select
            allowClear
            placeholder="Filtrar por estado"
            style={{ width: 200 }}
            value={filtro}
            onChange={setFiltro}
            options={[
              { value: 'activa', label: 'Activa' },
              { value: 'pausada', label: 'Pausada' },
              { value: 'cancelada', label: 'Cancelada' },
            ]}
          />
        }
      >
        {isError ? (
          <ErrorAlert error={error} />
        ) : filtered.length === 0 && !isFetching ? (
          <EmptyState description="No hay suscripciones con ese filtro." />
        ) : (
          <Table
            rowKey="cliente_suscripcion_id"
            columns={columns}
            dataSource={filtered}
            pagination={false}
            loading={isFetching}
          />
        )}
      </Card>

      <ResultadoCobroModal
        open={resumen !== null}
        resumen={resumen}
        onClose={cerrarResumen}
      />
    </div>
  )
}
