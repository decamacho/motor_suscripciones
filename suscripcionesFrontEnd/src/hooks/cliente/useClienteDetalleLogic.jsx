import { useState } from "react";
import { useForm } from "react-hook-form";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { message } from "antd";
import { api } from "../../services/api";

export const useClienteDetalleLogic = (clienteId) => {
  const queryClient = useQueryClient();
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState(null);
  const { control, handleSubmit, reset } = useForm({
    defaultValues: {
      suscripcion_id: undefined,
      estado_cliente_suscripcion: undefined,
      resultado_forzado: undefined,
    },
  });

  const { data: cliente, isLoading, isFetching, isError, error } = useQuery({
    queryKey: ["cliente", clienteId],
    queryFn: () => api.get(`/clientes/${clienteId}`),
  });

  const { data: planes } = useQuery({
    queryKey: ["planes"],
    queryFn: () => api.get("/suscripciones"),
  });

  const invalidateCliente = () =>
    queryClient.invalidateQueries({ queryKey: ["cliente", clienteId] });

  const invalidateTodo = () => {
    invalidateCliente();
    queryClient.invalidateQueries({ queryKey: ["clientes"] });
    queryClient.invalidateQueries({ queryKey: ["suscripciones-cliente"] });
  };

  const asignarMutation = useMutation({
    mutationFn: async (values) => {
      const { estado_cliente_suscripcion, resultado_forzado } = values;
      if (editing) {
        return api.put(
          `/cliente-suscripciones/${editing.cliente_suscripcion_id}`,
          { estado_cliente_suscripcion },
        );
      }
      const relacion = await api.post("/cliente-suscripciones", {
        suscripcion_id: values.suscripcion_id,
        cliente_id: clienteId,
      });
      await api.post(
        `/cliente-suscripciones/${relacion.cliente_suscripcion_id}/cobrar`,
        { resultado: resultado_forzado ?? null },
      );
      return relacion;
    },
    onSuccess: () => {
      message.success(
        editing ? "Suscripción actualizada" : "Suscripción asignada",
      );
      setModalOpen(false);
      setEditing(null);
      reset();
      invalidateTodo();
    },
    onError: (e) => message.error(e.message),
  });

  const deleteMutation = useMutation({
    mutationFn: (id) => api.delete(`/cliente-suscripciones/${id}`),
    onSuccess: () => {
      message.success("Suscripción removida");
      invalidateTodo();
    },
    onError: (e) => message.error(e.message),
  });

  const openAssign = () => {
    setEditing(null);
    reset({
      suscripcion_id: undefined,
      estado_cliente_suscripcion: undefined,
      resultado_forzado: undefined,
    });
    setModalOpen(true);
  };

  const openEdit = (record) => {
    setEditing(record);
    reset({
      suscripcion_id: record.suscripcion_id,
      estado_cliente_suscripcion: record.estado_cliente_suscripcion,
      resultado_forzado: undefined,
    });
    setModalOpen(true);
  };

  const closeModal = () => {
    setModalOpen(false);
    setEditing(null);
  };

  const onSubmit = handleSubmit((values) => asignarMutation.mutate(values));

  return {
    state: {
      dataCliente: cliente,
      isLoadingCliente: isLoading,
      isFetchingCliente: isFetching,
      isErrorCliente: isError,
      errorCliente: error,
      planes,
      modalOpen,
      setModalOpen,
      editing,
      setEditing,
    },
    mutation: {
      asignarMutation,
      deleteMutation,
    },
    functions: {
      openAssign,
      openEdit,
      closeModal,
      onSubmit,
    },
    control,
  };
};
