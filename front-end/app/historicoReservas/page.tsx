"use client";

import React, { useEffect, useState } from "react";
import Layout from "../components/Layout";
import axios from "axios";
import { format } from "date-fns";
import { ptBR } from "date-fns/locale";
import withAuth from "../Hoc/withAuth";

// Interfaces para tipagem
interface HistoricoReserva {
  id: number;
  reserva_id: number;
  alteracoes: string;
  modificado_em: string;
  nome_usuario_reservado: string;
  nome_usuario_alteracao: string;
  hora_inicio: string | null;
  hora_fim: string | null;
}

interface User {
  id: number;
  nome: string;
  papel: string;
}

const HistoricoReservas: React.FC = () => {
  const [historicos, setHistoricos] = useState<HistoricoReserva[]>([]);
  const [usuario, setUsuario] = useState<User | null>(null);
  const [error, setError] = useState<string | null>(null);

  // Carregar o usuário do localStorage ao montar o componente
  useEffect(() => {
    const userData = localStorage.getItem("user");
    if (userData) {
      const parsedUser = JSON.parse(userData) as User;
      console.log("Usuário carregado:", parsedUser);
      setUsuario(parsedUser);
    } else {
      console.error("Usuário não encontrado no localStorage.");
      setError("Usuário não encontrado. Faça login novamente.");
    }
  }, []);

  // Buscar os históricos de reservas
  useEffect(() => {
    const fetchHistoricos = async () => {
      try {
        if (!usuario) return; // Espera até o usuário ser carregado

        console.log("Buscando histórico para o papel:", usuario.papel);

        const params = usuario.papel === "admin" ? {} : { usuario_id: usuario.id };

        const response = await axios.get<HistoricoReserva[]>(
          "http://127.0.0.1:8000/api/historico-reservas",
          { params }
        );

        setHistoricos(response.data);
      } catch (err) {
        console.error("Erro ao buscar histórico de reservas:", err);
        setError("Erro ao carregar o histórico de reservas.");
      }
    };

    if (usuario) {
      fetchHistoricos();
    }
  }, [usuario]);

  if (error) {
    return (
      <Layout>
        <p className="text-red-500">{error}</p>
      </Layout>
    );
  }

  return (
    <Layout>
      <div className="container mx-auto p-4">
        <h1 className="text-2xl font-bold mb-4">Histórico de Reservas</h1>
        {historicos.length === 0 ? (
          <p>Nenhum histórico encontrado.</p>
        ) : (
          <table className="table-auto w-full border-collapse border border-gray-300">
            <thead>
              <tr>
                <th className="border border-gray-300 px-4 py-2">Reserva ID</th>
                <th className="border border-gray-300 px-4 py-2">Usuário Responsável</th>
                <th className="border border-gray-300 px-4 py-2">Usuário Alteração</th>
                <th className="border border-gray-300 px-4 py-2">Hora Início</th>
                <th className="border border-gray-300 px-4 py-2">Hora Fim</th>
                <th className="border border-gray-300 px-4 py-2">Alterações</th>
                <th className="border border-gray-300 px-4 py-2">Modificado em</th>
              </tr>
            </thead>
            <tbody>
              {historicos.map((historico) => (
                <tr key={historico.id}>
                  <td className="border border-gray-300 px-4 py-2">{historico.reserva_id}</td>
                  <td className="border border-gray-300 px-4 py-2">{historico.nome_usuario_reservado || "Não especificado"}</td>
                  <td className="border border-gray-300 px-4 py-2">{historico.nome_usuario_alteracao || "Não especificado"}</td>
                  <td className="border border-gray-300 px-4 py-2">
                    {historico.hora_inicio && !isNaN(Date.parse(historico.hora_inicio))
                      ? format(new Date(historico.hora_inicio), "dd/MM/yyyy HH:mm", { locale: ptBR })
                      : "Não especificado"}
                  </td>
                  <td className="border border-gray-300 px-4 py-2">
                    {historico.hora_fim && !isNaN(Date.parse(historico.hora_fim))
                      ? format(new Date(historico.hora_fim), "dd/MM/yyyy HH:mm", { locale: ptBR })
                      : "Não especificado"}
                  </td>
                  <td className="border border-gray-300 px-4 py-2">{historico.alteracoes}</td>
                  <td className="border border-gray-300 px-4 py-2">
                    {historico.modificado_em && !isNaN(Date.parse(historico.modificado_em))
                      ? format(new Date(historico.modificado_em), "dd/MM/yyyy HH:mm", { locale: ptBR })
                      : "Não especificado"}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </Layout>
  );
};

export default withAuth(HistoricoReservas, ["admin"]);
