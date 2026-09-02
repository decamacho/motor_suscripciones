import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { api } from "../../services/api";
import { message } from "antd";

export const useCliente = ({ editing, setModalOpen, setEditing, reset }) => {
  const queryClient = useQueryClient();
  
  const invalidate = () => {
    queryClient.invalidateQueries({ queryKey: ["clientes"] });
    queryClient.invalidateQueries({ queryKey: ["suscripciones"] });
  };

  const {
    data: dataCliente,
    isLoading: isLoadingCliente,
    isFetching: isFetchingCliente,
    isError: isErrorCliente,
    error: errorCliente,
  } = useQuery({
    queryKey: ["clientes"],
    queryFn: () => api.get("/clientes"),
  });

  const {
    data: dataSuscripciones,
    isLoading: isLoadingSuscripciones,
  } = useQuery({
    queryKey: ["suscripciones"],
    queryFn: () => api.get("/suscripciones"),
  });

  const saveMutation = useMutation({
    mutationFn: async (values) => {
      const { suscripcion_id, resultado_forzado, ...clienteData } = values;
      if (editing) {
        return api.put(`/clientes/${editing.cliente_id}`, clienteData);
      }
      const nuevoCliente = await api.post("/clientes", clienteData);
      if (suscripcion_id) {
        const relacion = await api.post("/cliente-suscripciones", {
          cliente_id: nuevoCliente.cliente_id,
          suscripcion_id,
        });
        await api.post(
          `/cliente-suscripciones/${relacion.cliente_suscripcion_id}/cobrar`,
          { resultado: resultado_forzado ?? null },
        );
      }
      return nuevoCliente;
    },
    onSuccess: () => {
      message.success(editing ? "Cliente actualizado" : "Cliente creado");
      setModalOpen(false);
      setEditing(null);
      reset();
      invalidate();
    },
    onError: (e) => message.error(e.message),
  });

  const deleteMutation = useMutation({
    mutationFn: (id) => api.delete(`/clientes/${id}`),
    onSuccess: () => {
      message.success("Cliente eliminado");
      invalidate();
    },
    onError: (e) => message.error(e.message),
  });

  return {
    state: {
      dataCliente,
      isLoadingCliente,
      isFetchingCliente,
      isErrorCliente,
      errorCliente,
      dataSuscripciones,
      isLoadingSuscripciones,
    },
    mutation: {
      saveMutation,
      deleteMutation,
    },
  };
};
