import type { NodeType } from "../workflow/types";

type ToolbarProps = {
  onAddNode: (type: NodeType) => void;
  onExecute: () => void;
  onSave: () => void;
  isSaving: boolean;
  syncStatus: string;
};

const nodeButtonLabel: Record<NodeType, string> = {
  start: "🚀 Add Start Node",
  log: "📝 Add Log Node",
  color: "🎨 Add Color Node",
  name: "👤 Add Name Node",
  email: "✉️ Add Email Node",
  delay: "⏱️ Add Delay Node",
  condition: "🔀 Add Condition Node",
  http: "🌐 Add HTTP Node",
};

export function Toolbar({
  onAddNode,
  onExecute,
  onSave,
  isSaving,
  syncStatus,
}: ToolbarProps) {
  return (
    <header className="toolbar">
      <div>
        <h1>Minimal Workflow Builder</h1>
        <p>{syncStatus}</p>
      </div>

      <div className="toolbar-actions">
        <button type="button" onClick={() => onAddNode("log")}>
          {nodeButtonLabel.log}
        </button>
        <button type="button" onClick={() => onAddNode("color")}>
          {nodeButtonLabel.color}
        </button>
        <button type="button" onClick={() => onAddNode("name")}>
          {nodeButtonLabel.name}
        </button>
        <button type="button" onClick={() => onAddNode("email")}>
          {nodeButtonLabel.email}
        </button>
        <button type="button" onClick={() => onAddNode("delay")}>
          {nodeButtonLabel.delay}
        </button>
        <button type="button" onClick={() => onAddNode("condition")}>
          {nodeButtonLabel.condition}
        </button>
        <button type="button" onClick={() => onAddNode("http")}>
          {nodeButtonLabel.http}
        </button>
        <button type="button" className="primary" onClick={onExecute}>
          ▶️ Execute
        </button>
        <button type="button" onClick={onSave} disabled={isSaving}>
          {isSaving ? "💾 Saving..." : "💾 Save Workflow"}
        </button>
      </div>
    </header>
  );
}
