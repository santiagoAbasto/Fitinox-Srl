import 'maplibre-gl/dist/maplibre-gl.css';
import * as maplibregl from 'maplibre-gl';
import { useEffect, useRef } from 'react';

type Country = {code:string;name?:string;latitude?:number;longitude?:number;sessions:number};

export default function AnalyticsGeoMap({countries}:{countries:Country[]}) {
    const container=useRef<HTMLDivElement>(null);
    useEffect(()=>{
        if(!container.current) return;
        const map=new maplibregl.Map({container:container.current,style:'https://demotiles.maplibre.org/style.json',center:[0,18],zoom:1.1,attributionControl:false});
        map.addControl(new maplibregl.NavigationControl({showCompass:false}),'top-right');
        map.on('load',()=>{
            const features=countries.filter(c=>Number.isFinite(Number(c.latitude))&&Number.isFinite(Number(c.longitude))).map(c=>({type:'Feature' as const,geometry:{type:'Point' as const,coordinates:[Number(c.longitude),Number(c.latitude)]},properties:{...c}}));
            map.addSource('visitors',{type:'geojson',data:{type:'FeatureCollection',features},cluster:true,clusterRadius:48});
            map.addLayer({id:'clusters',type:'circle',source:'visitors',filter:['has','point_count'],paint:{'circle-color':'#1954b7','circle-radius':['step',['get','point_count'],18,20,24,100,32],'circle-opacity':.82}});
            map.addLayer({id:'points',type:'circle',source:'visitors',filter:['!', ['has','point_count']],paint:{'circle-color':'#19a974','circle-radius':['interpolate',['linear'],['get','sessions'],1,6,100,22],'circle-opacity':.78,'circle-stroke-width':2,'circle-stroke-color':'#fff'}});
        });
        return()=>map.remove();
    },[countries]);
    return <div className="relative"><div ref={container} className="h-[360px] overflow-hidden rounded-xl" aria-label="Mapa mundial aproximado de visitantes"/>{!countries.length&&<div className="pointer-events-none absolute inset-0 grid place-items-center bg-white/75 text-center text-xs text-[#64757e] backdrop-blur-[2px]"><span><strong className="block text-sm text-[#243f4f]">Geografía aún no disponible</strong>Cloudflare o MaxMind aportarán ubicaciones aproximadas.</span></div>}</div>;
}
