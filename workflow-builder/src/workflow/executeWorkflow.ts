import type { Edge, WorkflowNode } from "./types";

const sleep = (ms: number) =>
  new Promise<void>((resolve) => {
    setTimeout(resolve, ms);
  });

export async function executeWorkflow(nodes: WorkflowNode[], edges: Edge[]) {
  const nodeMap = new Map(nodes.map((node) => [node.id, node]));
  const incoming = new Map<string, number>();

  nodes.forEach((node) => incoming.set(node.id, 0));
  edges.forEach((edge) => {
    incoming.set(edge.to, (incoming.get(edge.to) ?? 0) + 1);
  });

  const adjacency = new Map<string, string[]>();
  edges.forEach((edge) => {
    const list = adjacency.get(edge.from) ?? [];
    list.push(edge.to);
    adjacency.set(edge.from, list);
  });

  const explicitStartNodes = nodes.filter((node) => node.type === "start");
  const entryNodes =
    explicitStartNodes.length > 0
      ? explicitStartNodes
      : nodes.filter((node) => (incoming.get(node.id) ?? 0) === 0);

  const visited = new Set<string>();
  const evaluateCondition = (expression: string): boolean => {
    const trimmed = expression.trim();
    if (!trimmed) return false;

    const equalsParts = trimmed.split("==");
    if (equalsParts.length === 2) {
      return equalsParts[0].trim() === equalsParts[1].trim();
    }

    const notEqualsParts = trimmed.split("!=");
    if (notEqualsParts.length === 2) {
      return notEqualsParts[0].trim() !== notEqualsParts[1].trim();
    }

    return trimmed.toLowerCase() === "true";
  };

  const runNode = async (nodeId: string): Promise<void> => {
    if (visited.has(nodeId)) return;
    visited.add(nodeId);

    const node = nodeMap.get(nodeId);
    if (!node) return;

    if (node.type === "log") {
      console.log(node.text);
    } else if (node.type === "color") {
      console.log(`%c${node.text}`, `color: ${node.color}`);
    } else if (node.type === "name") {
      console.log(`name: "${node.text}"`);
    } else if (node.type === "email") {
      console.log(`email: "${node.text}"`);
    } else if (node.type === "delay") {
      const delayMs = Number.parseInt(node.text, 10);
      const waitMs = Number.isFinite(delayMs) && delayMs > 0 ? delayMs : 0;
      console.log(`delay: waiting ${waitMs}ms`);
      if (waitMs > 0) {
        await sleep(waitMs);
      }
    } else if (node.type === "condition") {
      const result = evaluateCondition(node.text);
      console.log(`condition (${node.text}) => ${result}`);

      const branchNodes = adjacency.get(nodeId) ?? [];
      const chosenNode = result ? branchNodes[0] : branchNodes[1];

      if (chosenNode) {
        await runNode(chosenNode);
      }
      return;
    } else if (node.type === "http") {
      const url = node.text.trim();
      if (!url) {
        console.error("http: missing URL");
      } else {
        try {
          const response = await fetch(url);
          const contentType = response.headers.get("content-type") ?? "";
          const isJson = contentType.includes("application/json");
          const responseBody = isJson
            ? JSON.stringify(await response.json())
            : await response.text();

          console.log(`http ${response.status} ${response.statusText} -> ${url}`);
          console.log(responseBody);
        } catch (error) {
          console.error(
            `http request failed for ${url}: ${
              error instanceof Error ? error.message : "unknown error"
            }`,
          );
        }
      }
    }
    // start node is intentionally "empty" and only forwards flow

    for (const nextNodeId of adjacency.get(nodeId) ?? []) {
      await runNode(nextNodeId);
    }
  };

  for (const node of entryNodes) {
    await runNode(node.id);
  }
}
