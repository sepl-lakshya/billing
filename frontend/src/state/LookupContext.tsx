import { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { apiGet } from '../api/client';
import { Lookups } from '../api/types';

const empty: Lookups = {
  months: [], years: [], states: [], gstSlabs: [], subscriptionTerms: [], billingTerms: [],
  cloudCategories: [], adjustmentTypes: [],
  creditNoteTypes: [], debitNoteTypes: [], unitMeasures: [], discoveryStatuses: [],
  contractYears: [], contractMonths: [], billingProgress: [],
};

interface LookupCtx {
  lookups: Lookups;
  loading: boolean;
  reload: () => void;
}

const Ctx = createContext<LookupCtx>({ lookups: empty, loading: true, reload: () => {} });

export function LookupProvider({ children }: { children: ReactNode }) {
  const [lookups, setLookups] = useState<Lookups>(empty);
  const [loading, setLoading] = useState(true);

  const load = () => {
    setLoading(true);
    apiGet<Lookups>('/lookups')
      .then((d) => setLookups(d || empty))
      .catch(() => setLookups(empty))
      .finally(() => setLoading(false));
  };

  useEffect(() => { load(); }, []);

  return <Ctx.Provider value={{ lookups, loading, reload: load }}>{children}</Ctx.Provider>;
}

export const useLookups = () => useContext(Ctx);

/** Helper: resolve a lookup name by value. */
export function nameOf(list: { value: number; name: string }[], value: number | undefined): string {
  return list.find((x) => x.value === value)?.name ?? '';
}
