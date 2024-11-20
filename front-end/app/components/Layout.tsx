import Sidebar from '../components/Sidebar';

interface LayoutProps {
  children: React.ReactNode;
}

const Layout = ({ children }: LayoutProps) => {
  return (
    <div style={{ display: 'flex' }}>
      <Sidebar />
      <div style={{ marginLeft: '200px', padding: '20px' }}>
        {children}
      </div>
    </div>
  );
};

export default Layout;
