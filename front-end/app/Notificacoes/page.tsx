"use client";

import React, { useEffect, useState } from "react";
import Layout from "../components/Layout";
import "bootstrap/dist/css/bootstrap.min.css";

interface Notificacao {
  id: number;
  mensagem: string;
  tipo: string;
  criado_em: string;
}

const Notificacoes: React.FC = () => {
  const [notificacoes, setNotificacoes] = useState<Notificacao[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchNotificacoes = async (id: number) => {
    try {
      const response = await fetch(`http://127.0.0.1:8000/api/notificacoes/${id}`, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        throw new Error("Erro ao buscar notificações.");
      }

      const data = await response.json();
      setNotificacoes(data);
    } catch (error: any) {
      setError(error.message || "Erro desconhecido.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    // Recupera o ID do usuário do localStorage
    const storedUser = localStorage.getItem("user");
    if (!storedUser) {
      setError("Usuário não encontrado. Faça login novamente.");
      setLoading(false);
      return;
    }

    try {
      const user = JSON.parse(storedUser); 
      const userId = user.id; 
      fetchNotificacoes(userId);
    } catch (e) {
      setError("Erro ao processar os dados do usuário. Faça login novamente.");
      setLoading(false);
    }
  }, []);

  return (
    <Layout>
      <div className="container mt-4">
        <h1 className="text-center mb-4">Notificações</h1>
        {loading && <p>Carregando notificações...</p>}
        {error && <p className="text-danger">{error}</p>}
        {!loading && !error && notificacoes.length > 0 && (
          <div className="table-responsive">
            <table className="table table-striped table-bordered">
              <thead className="thead-dark">
                <tr>
                  <th>#</th>
                  <th>Mensagem</th>
                  <th>Tipo</th>
                  <th>Data</th>
                </tr>
              </thead>
              <tbody>
                {notificacoes.map((notificacao, index) => (
                  <tr key={notificacao.id}>
                    <td>{index + 1}</td>
                    <td>{notificacao.mensagem}</td>
                    <td>{notificacao.tipo}</td>
                    <td>{new Date(notificacao.criado_em).toLocaleString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
        {!loading && !error && notificacoes.length === 0 && (
          <p className="text-center">Você não tem notificações no momento.</p>
        )}
      </div>
    </Layout>
  );
};

export default Notificacoes;
