import { Button, Card, Form, Input, Modal, Popconfirm, Select, Table } from "antd";
import { Controller, useWatch } from "react-hook-form";
import { PlusOutlined } from "@ant-design/icons";
import { useNavigate } from "react-router-dom";
import { ErrorAlert } from "../../components/Estados";
import ResultadoCobroSelect from "../../components/ResultadoCobroSelect";
import { useClienteLogic } from "../../hooks/cliente/useClienteLogic";
import { TYPE_FORM_CLIENTE } from "../../utils/clienteType";

export default function Clientes() {
  const navigate = useNavigate();
  const { state, mutation, functions, control } = useClienteLogic();

  const suscripcionId = useWatch({ control, name: "suscripcion_id" });

  const columns = [
    { title: "Nombre", dataIndex: "cliente_nombre" },
    { title: "Correo", dataIndex: "cliente_correo" },
    { title: "Documento", dataIndex: "cliente_documento" },
    { title: "Teléfono", dataIndex: "cliente_telefono" },
    {
      title: "Suscripciones",
      dataIndex: "suscripciones_count",
      width: 140,
      render: (v) => v ?? 0,
    },
    {
      title: "Acciones",
      key: "acciones",
      width: 220,
      render: (_, record) => (
        <div className="flex gap-2">
          <Button
            type="link"
            onClick={() => navigate(`/clientes/${record.cliente_id}`)}
          >
            Suscripciones
          </Button>
          <Button type="link" onClick={() => functions.openEdit(record)}>
            Editar
          </Button>
          <Popconfirm
            title="¿Eliminar este cliente?"
            onConfirm={() => mutation.deleteMutation.mutate(record.cliente_id)}
          >
            <Button type="link" danger>
              Eliminar
            </Button>
          </Popconfirm>
        </div>
      ),
    },
  ];

  return (
    <Card
      title="Clientes"
      extra={
        <Button
          type="primary"
          icon={<PlusOutlined />}
          onClick={functions.openCreate}
        >
          Nuevo cliente
        </Button>
      }
    >
      {state.isErrorCliente ? (
        <ErrorAlert error={state.errorCliente} />
      ) : (
        <Table
          rowKey="cliente_id"
          columns={columns}
          dataSource={state.dataCliente || []}
          pagination={false}
          loading={state.isFetchingCliente || mutation.saveMutation.isPending || mutation.deleteMutation.isPending}
        />
      )}
      <Modal
        title={state.editing ? "Editar cliente" : "Nuevo cliente"}
        open={state.modalOpen}
        onCancel={functions.closeModal}
        onOk={functions.onSubmit}
        confirmLoading={mutation.saveMutation.isPending}
        forceRender
      >
        <Form layout="vertical">
          {Object.values(TYPE_FORM_CLIENTE).map((field) => (
            <Controller
              key={field.name}
              name={field.name}
              control={control}
              rules={{...field.required && { required: field.required }, ...field.pattern && { pattern: field.pattern }}}
              render={({ field: inputField, fieldState }) => (
                <Form.Item
                  label={field.label}
                  validateStatus={fieldState.error ? "error" : ""}
                  help={fieldState.error?.message}
                >
                  <Input
                    {...inputField}
                    placeholder={field.placeholder}
                    maxLength={field.name === "cliente_nombre" || field.name === "cliente_correo" ? undefined : 10}
                  />
                </Form.Item>
              )}
            />
          ))}
          {!state.editing && (
            <Controller
              name="suscripcion_id"
              control={control}
              render={({ field }) => (
                <Form.Item label="Suscripción (opcional)">
                  <Select
                    {...field}
                    placeholder="Selecciona un plan (opcional)"
                    allowClear
                    options={(state.dataPlanes || []).map((p) => ({
                      value: p.suscripcion_id,
                      label: `${p.suscripcion_nombre} (${p.suscripcion_periodo})`,
                    }))}
                  />
                </Form.Item>
              )}
            />
          )}
          {!state.editing && suscripcionId && (
            <Controller
              name="resultado_forzado"
              control={control}
              render={({ field }) => (
                <Form.Item label="Resultado del cobro">
                  <ResultadoCobroSelect
                    {...field}
                    placeholder="Automático (aleatorio)"
                  />
                </Form.Item>
              )}
            />
          )}
        </Form>
      </Modal>
    </Card>
  );
}
