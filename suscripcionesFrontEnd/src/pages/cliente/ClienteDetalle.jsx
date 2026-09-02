import {
  Button,
  Card,
  Descriptions,
  Form,
  Modal,
  Popconfirm,
  Select,
  Space,
  Spin,
  Table,
} from "antd";
import { Controller, useWatch } from "react-hook-form";
import { PlusOutlined } from "@ant-design/icons";
import { useNavigate, useParams } from "react-router-dom";
import EstadoTag from "../../components/EstadoTag";
import ResultadoCobroSelect from "../../components/ResultadoCobroSelect";
import { ErrorAlert } from "../../components/Estados";
import { formatoFecha } from "../../utils/formatoFecha";
import { useClienteDetalleLogic } from "../../hooks/cliente/useClienteDetalleLogic";

export default function ClienteDetalle() {
  const { clienteId } = useParams();
  const navigate = useNavigate();
  const { state, mutation, functions, control } = useClienteDetalleLogic(
    clienteId,
  );

  const suscripcionId = useWatch({ control, name: "suscripcion_id" });

  if (state.isLoadingCliente) {
    return (
      <div className="flex items-center justify-center py-16">
        <Spin size="large" />
      </div>
    );
  }

  if (state.isErrorCliente) {
    return (
      <div className="space-y-4">
        <ErrorAlert error={state.errorCliente} />
        <Button onClick={() => navigate("/")}>Volver a clientes</Button>
      </div>
    );
  }

  const cliente = state.dataCliente;
  const subtipos = cliente?.cliente_suscripciones || [];
  const suscripcionesAsignadas = subtipos.map((s) => s.suscripcion_id);
  const suscripcionesDisponibles = (state.suscripciones || []).filter(
    (p) => !suscripcionesAsignadas.includes(p.suscripcion_id)
  );

  const columns = [
    {
      title: "Suscripción",
      dataIndex: "suscripcion_nombre",
      render: (v, r) => (
        <span>
          {r.suscripcion.suscripcion_nombre}
        </span>
      ),
    },
    {
      title: "Precio",
      dataIndex: "suscripcion_precio",
      render: (v, r) => `$ ${r.suscripcion.suscripcion_precio}`,
    },
    {
      title: "Estado",
      dataIndex: "estado_cliente_suscripcion",
      render: (v) => <EstadoTag value={v} />,
    },
    {
      title: "Último cobro",
      dataIndex: "fecha_ultimo_cobro",
      render: (v) => formatoFecha(v),
    },
    {
      title: "Próximo cobro",
      dataIndex: "fecha_proximo_cobro",
      render: (v) => formatoFecha(v),
    },
    {
      title: "Acciones",
      key: "acciones",
      width: 240,
      render: (_, record) => (
        <div className="flex gap-2">
          <Button
            type="link"
            onClick={() =>
              navigate(`/suscripciones/${record.cliente_suscripcion_id}`, {
                state: { clienteId },
              })
            }
          >
            Cobros
          </Button>
          <Button type="link" onClick={() => functions.openEdit(record)}>
            Editar
          </Button>
          <Popconfirm
            title="¿Remover esta suscripción?"
            onConfirm={() =>
              mutation.deleteMutation.mutate(record.cliente_suscripcion_id)
            }
          >
            <Button type="link" danger>
              Remover
            </Button>
          </Popconfirm>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-6">
      <Space>
        <Button onClick={() => navigate("/")}>← Volver</Button>
      </Space>

      <Card title="Datos del cliente">
        <Descriptions bordered column={{ xs: 1, sm: 2, md: 2 }}>
          <Descriptions.Item label="Nombre">
            {cliente.cliente_nombre}
          </Descriptions.Item>
          <Descriptions.Item label="Correo">
            {cliente.cliente_correo}
          </Descriptions.Item>
          <Descriptions.Item label="Documento">
            {cliente.cliente_documento}
          </Descriptions.Item>
          <Descriptions.Item label="Teléfono">
            {cliente.cliente_telefono}
          </Descriptions.Item>
        </Descriptions>
      </Card>

      <Card
        title={`Suscripciones (${subtipos.length})`}
        extra={
          <Button
            type="primary"
            icon={<PlusOutlined />}
            onClick={functions.openAssign}
            disabled={suscripcionesDisponibles.length === 0}
          >
            Añadir suscripción
          </Button>
        }
      >
        <Table
          rowKey="cliente_suscripcion_id"
          columns={columns}
          dataSource={subtipos}
          pagination={false}
          loading={state.isFetchingCliente || mutation.asignarMutation.isPending || mutation.deleteMutation.isPending}
          locale={{
            emptyText:
              "Este cliente no tiene suscripciones. Crea suscripciones primero y luego asigna una.",
          }}
        />
      </Card>

      <Modal
        title={
          state.editing ? "Editar suscripción" : "Añadir suscripción"
        }
        open={state.modalOpen}
        onCancel={functions.closeModal}
        onOk={functions.onSubmit}
        confirmLoading={mutation.asignarMutation.isPending}
        forceRender
      >
        <Form layout="vertical">
          <Controller
            name="suscripcion_id"
            control={control}
            rules={{ required: "Selecciona un Suscripcion" }}
            render={({ field, fieldState }) => (
              <Form.Item
                label="Plan de suscripción"
                validateStatus={fieldState.error ? "error" : ""}
                help={fieldState.error?.message}
              >
                <Select
                  {...field}
                  placeholder="Selecciona el suscripción"
                  disabled={!!state.editing}
                  options={suscripcionesDisponibles.map((p) => ({
                    value: p.suscripcion_id,
                    label: `${p.suscripcion_nombre} (${p.suscripcion_periodo})`,
                  }))}
                />
              </Form.Item>
            )}
          />
          {!state.editing && suscripcionId && (
            <Controller
              name="resultado_forzado"
              control={control}
              render={({ field }) => (
                <Form.Item label="Resultado del cobro">
                  <ResultadoCobroSelect {...field} />
                </Form.Item>
              )}
            />
          )}
          {state.editing && (
            <Controller
              name="estado_cliente_suscripcion"
              control={control}
              rules={{ required: "Selecciona el estado" }}
              render={({ field, fieldState }) => (
                <Form.Item
                  label="Estado"
                  validateStatus={fieldState.error ? "error" : ""}
                  help={fieldState.error?.message}
                >
                  <Select
                    {...field}
                    placeholder="Estado de la suscripción"
                    options={[
                      { value: "activa", label: "Activa" },
                      { value: "pausada", label: "Pausada" },
                      { value: "cancelada", label: "Cancelada" },
                    ]}
                  />
                </Form.Item>
              )}
            />
          )}
        </Form>
      </Modal>
    </div>
  );
}
