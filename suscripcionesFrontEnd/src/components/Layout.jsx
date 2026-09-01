import { Layout, Menu, Typography } from 'antd'
import {
  TeamOutlined,
  TagsOutlined,
  OrderedListOutlined,
} from '@ant-design/icons'
import { Link, Outlet, useLocation } from 'react-router-dom'

const items = [
  { key: '/', icon: <TeamOutlined />, label: <Link to="/">Clientes</Link> },
  {
    key: '/suscripcion',
    icon: <TagsOutlined />,
    label: <Link to="/suscripcion">Suscripción</Link>,
  },
  {
    key: '/suscripciones',
    icon: <OrderedListOutlined />,
    label: <Link to="/suscripciones">Suscripciones</Link>,
  },
]

const MENU_KEYS = ['/suscripciones', '/suscripcion', '/']

export default function AppLayout() {
  const { pathname } = useLocation()
  const selectedKey =
    MENU_KEYS.find((k) => k !== '/' && pathname.startsWith(k)) || '/'

  return (
    <Layout style={{ minHeight: '100vh' }}>
      <Layout.Sider
        className="!bg-slate-800"
        width={220}
      >
        <div className="flex items-center justify-center gap-2 px-4 py-4 text-white">
          <Typography.Title
            level={5}
            className="!m-0 !text-white"
            style={{ color: '#fff' }}
          >
            Sistema Suscripciones
          </Typography.Title>
        </div>
        <Menu
          theme="dark"
          mode="inline"
          selectedKeys={[selectedKey]}
          items={items}
          className="!bg-slate-800"
        />
      </Layout.Sider>
      <Layout>
        <Layout.Content className="p-6">
          <div className="mx-auto max-w-6xl">
            <Outlet />
          </div>
        </Layout.Content>
      </Layout>
    </Layout>
  )
}
