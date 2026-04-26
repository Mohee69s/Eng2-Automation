import type { NodeType } from "../workflow/types";

type ToolbarProps = {
  onAddNode: (type: NodeType) => void;
  onExecute: () => void;
  onSave: () => void;
  isSaving: boolean;
  syncStatus: string;
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
          Add Log Node
        </button>
        <button type="button" onClick={() => onAddNode("color")}>
          Add Color Node
        </button>
        <button type="button" className="primary" onClick={onExecute}>
          Execute
        </button>
        <button type="button" onClick={onSave} disabled={isSaving}>
          {isSaving ? "Saving..." : "Save Workflow"}
        </button>
      </div>
    </header>
  );
}
