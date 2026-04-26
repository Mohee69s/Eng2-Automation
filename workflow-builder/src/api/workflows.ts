import type { Edge, WorkflowNode } from "../workflow/types";

export type WorkflowRecord = {
  id: number;
  name: string;
  description: string | null;
  nodes: WorkflowNode[];
  edges: Edge[];
};

type WorkflowPayload = {
  name: string;
  description?: string;
  nodes: WorkflowNode[];
  edges: Edge[];
};

const API_BASE_URL =
  process.env.REACT_APP_API_BASE_URL?.replace(/\/$/, "") ??
  "http://localhost:8000/api";

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: {
      "Content-Type": "application/json",
      ...(init?.headers ?? {}),
    },
    ...init,
  });

  if (!response.ok) {
    throw new Error(`Request failed (${response.status})`);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return (await response.json()) as T;
}

export async function listWorkflows(): Promise<WorkflowRecord[]> {
  const json = await request<{ data: WorkflowRecord[] }>("/workflows");
  return json.data;
}

export async function getWorkflow(id: number): Promise<WorkflowRecord> {
  const json = await request<{ data: WorkflowRecord }>(`/workflows/${id}`);
  return json.data;
}

export async function createWorkflow(
  payload: WorkflowPayload,
): Promise<WorkflowRecord> {
  const json = await request<{ data: WorkflowRecord }>("/workflows", {
    method: "POST",
    body: JSON.stringify(payload),
  });

  return json.data;
}

export async function updateWorkflow(
  id: number,
  payload: WorkflowPayload,
): Promise<WorkflowRecord> {
  const json = await request<{ data: WorkflowRecord }>(`/workflows/${id}`, {
    method: "PUT",
    body: JSON.stringify(payload),
  });

  return json.data;
}
