import {
  Button,
  Card,
  Form,
  Input,
  InputNumber,
  Modal,
  Popconfirm,
  Select,
  Table,
} from "antd";
import { Controller } from "react-hook-form";
import { PlusOutlined } from "@ant-design/icons";
import EstadoTag from "../../components/EstadoTag";
import { ErrorAlert } from "../../components/Estados";
import { useSuscripcionLogic } from "../../hooks/suscripcion/useSuscripcionLogic";
import { TYPE_FORM_SUSCRIPCION } from "../../utils/suscripcionType";

export default function Suscripcion() {
  const { state, mutation, functions, control } = useSuscripcionLogic();

  const columns = [
    { title: "Nombre", dataIndex: "suscripcion_nombre" },
    { title: "Descripción", dataIndex: "suscripcion_descripcion" },
    {
      title: "Precio",
      dataIndex: "suscripcion_precio",
      render: (v) => `${v} $`,
    },
    {
      title: "Periodo",
      dataIndex: "suscripcion_periodo",
      render: (v) => <EstadoTag value={v} />,
    },
    {
      title: "Acciones",
      key: "acciones",
      width: 180,
      render: (_, record) => (
        <div className="flex gap-2">
          <Button type="link" onClick={() => functions.openEdit(record)}>
            Editar
          </Button>
          <Popconfirm
            title="¿Eliminar este plan?"
            onConfirm={() => mutation.deleteMutationSuscripcion.mutate(record.suscripcion_id)}
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
      title="Planes de Suscripción"
      extra={
        <Button
          type="primary"
          icon={<PlusOutlined />}
          onClick={functions.openCreate}
        >
          Nuevo plan
        </Button>
      }
    >
      {state.isError ? (
        <ErrorAlert error={state.error} />
      ) : (
        <Table
          rowKey="suscripcion_id"
          columns={columns}
          dataSource={state.data || []}
          pagination={false}
          loading={state.isFetching || mutation.saveMutationSuscripcion.isPending || mutation.deleteMutationSuscripcion.isPending}
        />
      )}
      <Modal
        title={state.editing ? "Editar suscripcion" : "Nuevo suscripcion"}
        open={state.modalOpen}
        onCancel={functions.closeModal}
        onOk={functions.onSubmit}
        confirmLoading={mutation.saveMutationSuscripcion.isPending}
        forceRender
      >
        <Form layout="vertical">
          <Controller
            name={TYPE_FORM_SUSCRIPCION.nombre.name}
            control={control}
            rules={{ required: TYPE_FORM_SUSCRIPCION.nombre.required }}
            render={({ field, fieldState }) => (
              <Form.Item
                label={TYPE_FORM_SUSCRIPCION.nombre.label}
                validateStatus={fieldState.error ? "error" : ""}
                help={fieldState.error?.message}
              >
                <Input {...field} placeholder={TYPE_FORM_SUSCRIPCION.nombre.placeholder} />
              </Form.Item>
            )}
          />
          <Controller
            name={TYPE_FORM_SUSCRIPCION.descripcion.name}
            control={control}
            rules={{ required: TYPE_FORM_SUSCRIPCION.descripcion.required }}
            render={({ field, fieldState }) => (
              <Form.Item
                label={TYPE_FORM_SUSCRIPCION.descripcion.label}
                validateStatus={fieldState.error ? "error" : ""}
                help={fieldState.error?.message}
              >
                <Input.TextArea {...field} rows={2} />
              </Form.Item>
            )}
          />
          <Controller
            name={TYPE_FORM_SUSCRIPCION.precio.name}
            control={control}
            rules={{ required: TYPE_FORM_SUSCRIPCION.precio.required }}
            render={({ field, fieldState }) => (
              <Form.Item
                label={TYPE_FORM_SUSCRIPCION.precio.label}
                validateStatus={fieldState.error ? "error" : ""}
                help={fieldState.error?.message}
              >
                <InputNumber
                  {...field}
                  min={1}
                  style={{ width: "100%" }}
                  addonAfter="$"
                />
              </Form.Item>
            )}
          />
          <Controller
            name={TYPE_FORM_SUSCRIPCION.periodo.name}
            control={control}
            rules={{ required: TYPE_FORM_SUSCRIPCION.periodo.required }}
            render={({ field, fieldState }) => (
              <Form.Item
                label={TYPE_FORM_SUSCRIPCION.periodo.label}
                validateStatus={fieldState.error ? "error" : ""}
                help={fieldState.error?.message}
              >
                <Select
                  {...field}
                  placeholder={TYPE_FORM_SUSCRIPCION.periodo.placeholder}
                  options={TYPE_FORM_SUSCRIPCION.periodo.options}
                />
              </Form.Item>
            )}
          />
        </Form>
      </Modal>
    </Card>
  );
}
