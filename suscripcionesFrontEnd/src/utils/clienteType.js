export const TYPE_FORM_CLIENTE = {
  nombre: {
    name: "cliente_nombre",
    label: "Nombre",
    required: "El campo nombre es obligatorio",
    placeholder: "Ejemplo: Nombre Apellido"
  },
  correo: {
    name: "cliente_correo",
    label: "Correo",
    required: "El campo correo es obligatorio",
    pattern: { value: /^\S+@\S+\.\S+$/, message: "El correo debe ser válido" },
    placeholder: "Ejemplo: correo@dominio.com"
  },
  documento: {
    name: "cliente_documento",
    label: "Documento",
    required: "El campo documento es obligatorio",
    pattern: { value: /^\d{1,10}$/, message: "El documento debe tener como máximo 10 dígitos" },
    placeholder: "Ejemplo: 1234567890"
  },
  telefono: {
    name: "cliente_telefono",
    label: "Teléfono",
    required: "El campo teléfono es obligatorio",
    pattern: { value: /^\d{10}$/, message: "El teléfono debe tener 10 dígitos" },
    placeholder: "Ejemplo: 3001000000"
  },
};

export const INITIAL_VALUES_FORM_CLIENTES = {
  cliente_nombre: "",
  cliente_correo: "",
  cliente_documento: "",
  cliente_telefono: "",
};
