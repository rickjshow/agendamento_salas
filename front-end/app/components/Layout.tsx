import Sidebar from "../components/Sidebar";

interface LayoutProps {
  children: React.ReactNode;
}

const Layout = ({ children }: LayoutProps) => {
  return (
    <div className="d-flex" style={{ minHeight: "100vh" }}>
      {/* Sidebar fixa no lado esquerdo */}
      <div className="flex-shrink-0 bg-dark text-white" style={{ width: "250px" }}>
        <Sidebar />
      </div>

      {/* Conteúdo centralizado */}
      <div
        className="flex-grow-1 d-flex flex-column align-items-center justify-content-start"
        style={{ padding: "20px" }}
      >
        {children}
      </div>
    </div>
  );
};

export default Layout;
