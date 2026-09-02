import { useState } from "react";
import { useForm } from "react-hook-form";
import { useSuscripcion } from "./useSuscripcion";
import { INITIAL_VALUES_FORM_SUSCRIPCION } from "../../utils/suscripcionType";

export const useSuscripcionLogic = () => {
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState(null);
  const { control, handleSubmit, reset } = useForm({
    defaultValues: INITIAL_VALUES_FORM_SUSCRIPCION,
  });

  const { state, mutation } = useSuscripcion({
    editing,
    setModalOpen,
    setEditing,
    reset,
  });

  const openCreate = () => {
    setEditing(null);
    reset(INITIAL_VALUES_FORM_SUSCRIPCION);
    setModalOpen(true);
  };

  const openEdit = (record) => {
    setEditing(record);
    reset({
      suscripcion_nombre: record.suscripcion_nombre,
      suscripcion_descripcion: record.suscripcion_descripcion,
      suscripcion_precio: record.suscripcion_precio,
      suscripcion_periodo: record.suscripcion_periodo,
    });
    setModalOpen(true);
  };

  const closeModal = () => {
    setModalOpen(false);
    setEditing(null);
  };

  const onSubmit = handleSubmit((values) => mutation.saveMutationSuscripcion.mutate(values));

  return {
    state: {
      data: state.dataSuscripcion,
      isLoading: state.isLoadingSuscripcion,
      isFetching: state.isFetchingSuscripcion,
      isError: state.isErrorSuscripcion,
      error: state.errorSuscripcion,
      modalOpen,
      setModalOpen,
      editing,
      setEditing,
    },
    functions: {
      openCreate,
      openEdit,
      closeModal,
      onSubmit,
    },
    mutation,
    control,
  };
};
