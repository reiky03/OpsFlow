export function StatusBadge({ value }: { value: string }) {
  return <span className={`badge badge-${value.toLowerCase().replaceAll('_', '-')}`}>{value.replaceAll('_', ' ')}</span>
}
