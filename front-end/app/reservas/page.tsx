"use client";

import React, { useState, useEffect } from "react";
import axios from "axios";
import Layout from "../components/Layout";
import withAuth from "../Hoc/withAuth";

// Interfaces para tipagem
interface Ambiente {
  id: number;
  nome: string;
  tipo: string;
  status: string;
  descricao: string;
}

interface Reserva {
  id: number;
  usuario_id: number;
  ambiente_id: number;
  hora_inicio: string;
  hora_fim: string;
  status: string;
}

interface User {
  id: number;
  nome: string;
  papel: string; // "admin" ou "professor"
}

const Reservas: React.FC = () => {
  const [reservas, setReservas] = useState<Reserva[]>([]);
  const [ambientes, setAmbientes] = useState<Ambiente[]>([]);
  const [usuario, setUsuario] = useState<User | null>(null);
  const [horariosDisponiveis, setHorariosDisponiveis] = useState<Record<string, string[]>>({});
  const [modalAberto, setModalAberto] = useState(false);
  const [reservaAtual, setReservaAtual] = useState<Reserva | null>(null);
  const [form, setForm] = useState({
    ambiente_id: "",
    diaSelecionado: "",
    horario: "",
    status: "ativa",
  });

  useEffect(() => {
    const userData = localStorage.getItem("user");
    if (userData) {
      const parsedUser = JSON.parse(userData) as User;
      console.log("Usuário carregado:", parsedUser);
      setUsuario(parsedUser); // Atualiza o estado do usuário
    } else {
      console.error("Usuário não encontrado no localStorage.");
    }
  }, []); // Executa apenas ao montar o componente
  
  useEffect(() => {
    if (usuario) {
      console.log("Chamando fetchDados...");
      fetchDados(); // Chama fetchDados somente após `usuario` ser definido
    }
  }, [usuario]); // Executa sempre que `usuario` for atualizado
  
  const fetchDados = async () => {
    try {
      // Busca os ambientes
      const responseAmbientes = await axios.get<Ambiente[]>("http://localhost:8000/api/ambientes");
      setAmbientes(responseAmbientes.data);
  
      // Busca as reservas, enviando o ID do usuário como parâmetro
      const responseReservas = await axios.get<Reserva[]>(
        `http://localhost:8000/api/reservas?usuario_id=${usuario?.id}`
      );
      setReservas(responseReservas.data);
    } catch (error) {
      console.error("Erro ao buscar dados:", error);
    }
  };
  

  // Abrir modal para criar ou editar reserva
  const abrirModal = (reserva: Reserva | null = null) => {
    setReservaAtual(reserva);
    if (reserva) {
      setForm({
        ambiente_id: reserva.ambiente_id.toString(),
        diaSelecionado: reserva.hora_inicio.split("T")[0],
        horario: `${reserva.hora_inicio} - ${reserva.hora_fim}`,
        status: reserva.status,
      });
    } else {
      setForm({ ambiente_id: "", diaSelecionado: "", horario: "", status: "ativa" });
    }
    setModalAberto(true);
  };

  // Fechar modal
  const fecharModal = () => {
    setModalAberto(false);
    setReservaAtual(null);
  };

  // Atualizar formulário
  const atualizarFormulario = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const { name, value } = e.target;
    setForm({ ...form, [name]: value });

    if (name === "ambiente_id") {
      calcularHorariosDisponiveis(Number(value));
    }
  };

  const calcularHorariosDisponiveis = (ambienteId: number) => {
    const reservasDoAmbiente = reservas.filter((r) => r.ambiente_id === ambienteId);
    const horariosAgrupados: Record<string, string[]> = {};
  
    let diaAtual = new Date();
    const limiteDias = new Date();
    limiteDias.setDate(limiteDias.getDate() + 7); // Limite de 7 dias no futuro
  
    while (diaAtual <= limiteDias) {
      const data = diaAtual.toISOString().split("T")[0]; // Exemplo: 2024-11-22
      let horaAtual = new Date(diaAtual);
      horaAtual.setHours(8, 0, 0, 0); // Horário de abertura
  
      const horaFechamento = new Date(diaAtual);
      horaFechamento.setHours(18, 0, 0, 0); // Horário de fechamento
  
      horariosAgrupados[data] = []; // Inicializa o array para o dia
  
      while (horaAtual < horaFechamento) {
        const horaProxima = new Date(horaAtual);
        horaProxima.setHours(horaProxima.getHours() + 1);
  
        // Verifica se o horário está ocupado
        const ocupado = reservasDoAmbiente.some((reserva) => {
          const inicioReserva = new Date(reserva.hora_inicio.replace(" ", "T"));
          const fimReserva = new Date(reserva.hora_fim.replace(" ", "T"));
  
          return (
            (horaAtual >= inicioReserva && horaAtual < fimReserva) || // Início dentro do intervalo
            (horaProxima > inicioReserva && horaProxima <= fimReserva) || // Fim dentro do intervalo
            (horaAtual <= inicioReserva && horaProxima >= fimReserva) // Abrange o intervalo completo
          );
        });
  
        if (!ocupado) {
          // Adiciona somente os horários disponíveis
          horariosAgrupados[data].push(
            `${horaAtual.toISOString().slice(11, 16)} - ${horaProxima.toISOString().slice(11, 16)}`
          );
        }
  
        horaAtual.setHours(horaAtual.getHours() + 1);
      }
  
      diaAtual.setDate(diaAtual.getDate() + 1);
    }
  
    console.log("Horários Disponíveis por Dia:", horariosAgrupados); // Adicione este log para depuração
    setHorariosDisponiveis(horariosAgrupados); // Atualiza o estado com os horários disponíveis
  };
  
  

  const salvarReserva = async () => {
    try {
      if (!usuario?.id) {
        alert("Erro: Usuário não identificado. Por favor, faça login novamente.");
        return;
      }
  
      const [horaInicio, horaFim] = form.horario.split(" - ");
      const payload = {
        ambiente_id: form.ambiente_id,
        hora_inicio: `${form.diaSelecionado}T${horaInicio}`,
        hora_fim: `${form.diaSelecionado}T${horaFim}`,
        status: form.status,
        usuario_id: usuario.id, // Passa o ID do usuário para a API
      };
  
      console.log("Payload enviado:", payload);
  
      if (reservaAtual) {
        await axios.put(`http://localhost:8000/api/reservas/${reservaAtual.id}/edit`, payload);
      } else {
        await axios.post("http://localhost:8000/api/reservas/store", payload);
      }
      fetchDados();
      fecharModal();
    } catch (error: any) {
      if (error.response && error.response.status === 409) {
        alert(error.response.data.error || "Conflito de horário. Tente outro horário.");
      } else {
        console.error("Erro ao salvar reserva:", error.response?.data || error);
        alert("Erro inesperado ao salvar a reserva. Tente novamente.");
      }
    }
  };
  

  const excluirReserva = async (id: number) => {
    if (confirm("Tem certeza que deseja excluir esta reserva?")) {
      try {
        await axios.delete(`http://localhost:8000/api/reservas/${id}`);
        fetchDados();
      } catch (error) {
        console.error("Erro ao excluir reserva:", error);
      }
    }
  };
  

  // Filtrar reservas com base no tipo de usuário
  const reservasFiltradas =
    usuario?.papel === "admin"
      ? reservas
      : reservas.filter((r) => r.usuario_id === usuario?.id);

  return (
    <Layout>
      <div className="container mx-auto p-4">
        <h1 className="text-2xl font-bold mb-4">Gerenciamento de Reservas</h1>
        <button
          onClick={() => abrirModal()}
          className="bg-blue-500 text-white px-4 py-2 rounded mb-4"
        >
          Adicionar Reserva
        </button>
        <table className="table-auto w-full border-collapse border border-gray-300">
          <thead>
            <tr>
              <th className="border border-gray-300 px-4 py-2">Ambiente</th>
              <th className="border border-gray-300 px-4 py-2">Início</th>
              <th className="border border-gray-300 px-4 py-2">Fim</th>
              <th className="border border-gray-300 px-4 py-2">Status</th>
              <th className="border border-gray-300 px-4 py-2">Ações</th>
            </tr>
          </thead>
          <tbody>
            {reservasFiltradas.map((reserva) => (
              <tr key={reserva.id}>
                <td className="border border-gray-300 px-4 py-2">
                  {ambientes.find((a) => a.id === reserva.ambiente_id)?.nome ||
                    "Ambiente não encontrado"}
                </td>
                <td className="border border-gray-300 px-4 py-2">{reserva.hora_inicio}</td>
                <td className="border border-gray-300 px-4 py-2">{reserva.hora_fim}</td>
                <td className="border border-gray-300 px-4 py-2">{reserva.status}</td>
                <td className="border border-gray-300 px-4 py-2">
                  <button
                    onClick={() => abrirModal(reserva)}
                    className="bg-green-500 text-white px-2 py-1 rounded mr-2"
                  >
                    Editar
                  </button>
                  <button
                    onClick={() => excluirReserva(reserva.id)}
                    className="bg-red-500 text-white px-2 py-1 rounded"
                  >
                    Excluir
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {/* Modal de Formulário */}
        {modalAberto && (
          <div className="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div className="bg-white p-6 rounded shadow-lg w-full max-w-lg">
              <h2 className="text-xl font-bold mb-4">
                {reservaAtual ? "Editar Reserva" : "Adicionar Reserva"}
              </h2>
              <form>
                <div className="mb-4">
                  <label className="block mb-2">Ambiente</label>
                  <select
                    name="ambiente_id"
                    value={form.ambiente_id}
                    onChange={atualizarFormulario}
                    className="w-full border border-gray-300 px-3 py-2 rounded"
                    required
                  >
                    <option value="">Selecione um ambiente</option>
                    {ambientes.map((ambiente) => (
                      <option key={ambiente.id} value={ambiente.id}>
                        {ambiente.nome}
                      </option>
                    ))}
                  </select>
                </div>
                {form.ambiente_id && (
                  <div className="mb-4">
                    <label className="block mb-2">Dia</label>
                    <select
                      name="diaSelecionado"
                      value={form.diaSelecionado}
                      onChange={(e) => setForm({ ...form, diaSelecionado: e.target.value })}
                      className="w-full border border-gray-300 px-3 py-2 rounded"
                      required
                    >
                      <option value="">Selecione um dia</option>
                      {Object.keys(horariosDisponiveis).map((dia) => (
                        <option key={dia} value={dia}>
                          {dia}
                        </option>
                      ))}
                    </select>
                  </div>
                )}
                {form.diaSelecionado && (
                  <div className="mb-4">
                    <label className="block mb-2">Horários Disponíveis</label>
                    <div className="grid grid-cols-2 gap-2">
                      {horariosDisponiveis[form.diaSelecionado]?.map((horario, index) => (
                        <button
                          type="button"
                          key={index}
                          onClick={() => setForm({ ...form, horario })}
                          className={`px-4 py-2 border rounded ${
                            form.horario === horario
                              ? "bg-blue-500 text-white"
                              : "bg-gray-200 text-black"
                          }`}
                        >
                          {horario}
                        </button>
                      ))}
                    </div>
                  </div>
                )}
              </form>
              <div className="flex justify-end mt-4">
                <button
                  onClick={salvarReserva}
                  className="bg-blue-500 text-white px-4 py-2 rounded mr-2"
                >
                  Salvar
                </button>
                <button
                  onClick={fecharModal}
                  className="bg-gray-500 text-white px-4 py-2 rounded"
                >
                  Cancelar
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </Layout>
  );
};

export default withAuth(Reservas, ["admin", "professor"]);
