import { useState } from "react";
import { useCliente } from "./useCliente";
import { useForm } from "react-hook-form";
import { INITIAL_VALUES_FORM_CLIENTES } from "../../utils/clienteType";

export const useClienteLogic = () => {
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState(null);
  const { control, handleSubmit, reset } = useForm({
    defaultValues: {
      ...INITIAL_VALUES_FORM_CLIENTES,
      suscripcion_id: undefined,
      resultado_forzado: undefined,
    }
  });

  const { state, mutation } = useCliente({
    editing,
    setModalOpen,
    setEditing,
    reset,
  });

  const closeModal = () => {
    setModalOpen(false);
    setEditing(null);
  };

  const openCreate = () => {
    setEditing(null);
    reset({
      ...INITIAL_VALUES_FORM_CLIENTES,
      suscripcion_id: undefined,
      resultado_forzado: undefined,
    });
    setModalOpen(true);
  };

  const openEdit = (record) => {
    setEditing(record);
    reset({
      cliente_nombre: record.cliente_nombre,
      cliente_correo: record.cliente_correo,
      cliente_documento: record.cliente_documento,
      cliente_telefono: record.cliente_telefono,
    });
    setModalOpen(true);
  };

  const onSubmit = handleSubmit((values) =>
    mutation.saveMutation.mutate(values),
  );

  return {
    state: {
      dataCliente: state.dataCliente,
      isLoadingCliente: state.isLoadingCliente,
      isFetchingCliente: state.isFetchingCliente,
      isErrorCliente: state.isErrorCliente,
      errorCliente: state.errorCliente,
      suscripciones: state.dataSuscripciones,
      loadingSuscripciones: state.isLoadingSuscripciones,
      modalOpen,
      setModalOpen,
      editing,
      setEditing,
    },
    mutation,
    functions: {
      openCreate,
      openEdit,
      closeModal,
      onSubmit,
    },
    control,
  };
};
