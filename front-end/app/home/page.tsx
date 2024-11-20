"use client";

import Layout from "../components/Layout"; // Importando o Layout
import withAuth from "../Hoc/withAuth"; // Importando o HOC

const Home: React.FC = () => {
  return (
    <Layout>
      <div>
        <h1>Bem-vindo à Home</h1>
        {/* Conteúdo da página */}
      </div>
    </Layout>
  );
};

// Proteger a página usando o HOC
export default withAuth(Home); // Apenas usuários autenticados podem acessar
