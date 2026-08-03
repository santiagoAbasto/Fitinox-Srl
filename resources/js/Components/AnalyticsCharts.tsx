import { Area, AreaChart, Bar, BarChart, CartesianGrid, Cell, Legend, Pie, PieChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

const colors = ['#1954b7','#19a974','#ef9d34','#8667c7','#e35d6a','#4e7b8e'];
const compact = new Intl.NumberFormat('es-AR',{notation:'compact',maximumFractionDigits:1});
const tooltipStyle = { border:'1px solid #dce5ea', borderRadius:12, boxShadow:'0 16px 40px rgba(19,43,59,.12)', fontSize:12 };

export function AnalyticsAreaChart({data}:{data:Array<Record<string,unknown>>}) {
    if (!data.length) return <Empty/>;
    return <ResponsiveContainer width="100%" height={310}><AreaChart data={data} margin={{top:12,right:8,left:-22,bottom:0}}>
        <defs><linearGradient id="sessionsFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stopColor="#1954b7" stopOpacity={.3}/><stop offset="100%" stopColor="#1954b7" stopOpacity={0}/></linearGradient></defs>
        <CartesianGrid stroke="#e8eef1" vertical={false}/><XAxis dataKey="date" tick={{fontSize:11,fill:'#718089'}} axisLine={false} tickLine={false}/><YAxis tickFormatter={v=>compact.format(v)} tick={{fontSize:11,fill:'#718089'}} axisLine={false} tickLine={false}/>
        <Tooltip contentStyle={tooltipStyle}/><Legend iconType="circle" wrapperStyle={{fontSize:11}}/>
        <Area type="monotone" dataKey="sessions" name="Sesiones" stroke="#1954b7" strokeWidth={2.5} fill="url(#sessionsFill)" animationDuration={450}/>
        <Area type="monotone" dataKey="visitors" name="Visitantes" stroke="#19a974" strokeWidth={2} fill="transparent" animationDuration={450}/>
    </AreaChart></ResponsiveContainer>;
}

export function AnalyticsDonutChart({data}:{data:Array<{name:string,value:number}>}) {
    if (!data.length) return <Empty/>;
    return <ResponsiveContainer width="100%" height={250}><PieChart><Pie data={data} dataKey="value" nameKey="name" innerRadius={58} outerRadius={88} paddingAngle={3} stroke="none">
        {data.map((_,i)=><Cell key={i} fill={colors[i%colors.length]}/>)}
    </Pie><Tooltip contentStyle={tooltipStyle}/><Legend iconType="circle" wrapperStyle={{fontSize:11}}/></PieChart></ResponsiveContainer>;
}

export function AnalyticsBarChart({data, valueKey='views'}:{data:Array<Record<string,any>>,valueKey?:string}) {
    if (!data.length) return <Empty/>;
    return <ResponsiveContainer width="100%" height={Math.max(240,data.length*38)}><BarChart data={data} layout="vertical" margin={{left:8,right:18}}>
        <CartesianGrid stroke="#e8eef1" horizontal={false}/><XAxis type="number" hide/><YAxis type="category" dataKey="name" width={130} tick={{fontSize:10,fill:'#52636d'}} axisLine={false} tickLine={false}/>
        <Tooltip contentStyle={tooltipStyle}/><Bar dataKey={valueKey} name="Cantidad" fill="#1954b7" radius={[0,6,6,0]} animationDuration={400}/>
    </BarChart></ResponsiveContainer>;
}

export function Empty(){return <div className="grid h-[240px] place-items-center rounded-xl border border-dashed border-[#d6e0e5] bg-[#f8fafb] text-center text-xs text-[#6d7c84]"><span><strong className="block text-sm text-[#344b59]">Sin datos en este período</strong>La información aparecerá al recibir actividad real.</span></div>}
