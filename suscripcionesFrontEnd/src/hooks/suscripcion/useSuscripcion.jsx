import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { message } from "antd";
import { api } from "../../services/api";
import { useEffect } from "react";

export const useSuscripcion = ({
  editing,
  setModalOpen,
  setEditing,
  reset,
}) => {
  const queryClient = useQueryClient();

  const invalidate = () =>
    queryClient.invalidateQueries({ queryKey: ["suscripciones"] });

  useEffect(() => {
    const interval = setInterval(() => {
      queryClient.invalidateQueries({ queryKey: ["suscripciones"] });
      queryClient.invalidateQueries({ queryKey: ["suscripciones-cliente"] });
    }, 180000);
    return () => clearInterval(interval);
  }, [queryClient]);

  const {
    data: dataSuscripcion,
    isLoading: isLoadingSuscripcion,
    isFetching: isFetchingSuscripcion,
    isError: isErrorSuscripcion,
    error: errorSuscripcion,
  } = useQuery({
    queryKey: ["suscripciones"],
    queryFn: () => api.get("/suscripciones"),
  });

  const saveMutationSuscripcion = useMutation({
    mutationFn: (values) =>
      editing
        ? api.put(`/suscripciones/${editing.suscripcion_id}`, values)
        : api.post("/suscripciones", values),
    onSuccess: () => {
      message.success(editing ? "Plan actualizado" : "Plan creado");
      setModalOpen(false);
      setEditing(null);
      reset();
      invalidate();
    },
    onError: (e) => message.error(e.message),
  });

  const deleteMutationSuscripcion = useMutation({
    mutationFn: (id) => api.delete(`/suscripciones/${id}`),
    onSuccess: () => {
      message.success("Plan eliminado");
      invalidate();
    },
    onError: (e) => message.error(e.message),
  });

  return {
    state: {
      dataSuscripcion,
      isLoadingSuscripcion,
      isFetchingSuscripcion,
      isErrorSuscripcion,
      errorSuscripcion,
    },
    mutation: {
      saveMutationSuscripcion,
      deleteMutationSuscripcion,
    },
  };
};
