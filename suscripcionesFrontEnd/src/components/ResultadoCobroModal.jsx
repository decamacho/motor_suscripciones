import { Descriptions, Modal } from 'antd'

export default function ResultadoCobroModal({ open, resumen, onClose }) {
  const sinCobros = !resumen || resumen.procesadas === 0

  return (
    <Modal
      title="Resultado del motor de cobro"
      open={open}
      onCancel={onClose}
      onOk={onClose}
      okText="Aceptar"
      cancelButtonProps={{ style: { display: 'none' } }}
      forceRender
    >
      {sinCobros ? (
        <p>No había suscripciones pendientes de cobro.</p>
      ) : (
        <Descriptions column={1} bordered size="small">
          <Descriptions.Item label="Procesadas">
            {resumen.procesadas}
          </Descriptions.Item>
          <Descriptions.Item label="Aprobadas">
            {resumen.aprobadas}
          </Descriptions.Item>
          <Descriptions.Item label="Rechazadas">
            {resumen.rechazadas}
          </Descriptions.Item>
          <Descriptions.Item label="Timeout">
            {resumen.tiempo_expirado}
          </Descriptions.Item>
        </Descriptions>
      )}
    </Modal>
  )
}