export const TYPE_FORM_SUSCRIPCION = {
  nombre: {
    name: "suscripcion_nombre",
    label: "Nombre",
    required: "El campo nombre es obligatorio",
    placeholder: " Ejemplo: Suscripcion Básica mensual"
  },
  descripcion: {
    name: "suscripcion_descripcion",
    label: "Descripción",
    required: "El campo descripción es obligatorio",
  },
  precio: {
    name: "suscripcion_precio",
    label: "Precio",
    required: "El campo precio es obligatorio",
  },
  periodo: {
    name: "suscripcion_periodo",
    label: "Periodo",
    required: "El campo periodo es obligatorio",
    options: [
      { value: "mensual", label: "Mensual" },
      { value: "anual", label: "Anual" },
    ],
    placeholder: "Selecciona el periodo"
  },
};

export const INITIAL_VALUES_FORM_SUSCRIPCION = {
  suscripcion_nombre: "",
  suscripcion_descripcion: "",
  suscripcion_precio: 0,
  suscripcion_periodo: "",
};