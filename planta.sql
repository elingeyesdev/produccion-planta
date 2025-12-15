--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.5

-- Started on 2025-12-14 10:38:03

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 276 (class 1259 OID 124619)
-- Name: almacenaje; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.almacenaje (
    almacenaje_id integer NOT NULL,
    lote_id integer NOT NULL,
    ubicacion character varying(100) NOT NULL,
    condicion character varying(100) NOT NULL,
    cantidad numeric(15,4) NOT NULL,
    observaciones character varying(500),
    latitud_recojo numeric(10,8),
    longitud_recojo numeric(11,8),
    direccion_recojo character varying(500),
    referencia_recojo character varying(255),
    fecha_almacenaje timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    fecha_retiro timestamp(0) without time zone
);


ALTER TABLE public.almacenaje OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 123832)
-- Name: almacenaje_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.almacenaje_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.almacenaje_seq OWNER TO postgres;

--
-- TOC entry 282 (class 1259 OID 124694)
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- TOC entry 283 (class 1259 OID 124701)
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- TOC entry 256 (class 1259 OID 124328)
-- Name: categoria_materia_prima; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categoria_materia_prima (
    categoria_id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion character varying(255),
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.categoria_materia_prima OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 123812)
-- Name: categoria_materia_prima_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categoria_materia_prima_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categoria_materia_prima_seq OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 124318)
-- Name: cliente; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cliente (
    cliente_id integer NOT NULL,
    razon_social character varying(200) NOT NULL,
    nombre_comercial character varying(200),
    nit character varying(20),
    direccion character varying(255),
    telefono character varying(20),
    email character varying(100),
    contacto character varying(100),
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.cliente OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 123811)
-- Name: cliente_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cliente_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cliente_seq OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 124472)
-- Name: destino_pedido; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.destino_pedido (
    destino_id integer NOT NULL,
    pedido_id integer NOT NULL,
    direccion character varying(500) NOT NULL,
    referencia character varying(200),
    latitud numeric(10,8),
    longitud numeric(11,8),
    nombre_contacto character varying(200),
    telefono_contacto character varying(20),
    instrucciones_entrega text,
    almacen_origen_id integer,
    almacen_origen_nombre character varying(255),
    almacen_destino_id integer,
    almacen_destino_nombre character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    almacen_almacen_id bigint
);


ALTER TABLE public.destino_pedido OWNER TO postgres;

--
-- TOC entry 5219 (class 0 OID 0)
-- Dependencies: 267
-- Name: COLUMN destino_pedido.almacen_almacen_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.destino_pedido.almacen_almacen_id IS 'ID del almacén en sistema-almacen-PSIII';


--
-- TOC entry 231 (class 1259 OID 123823)
-- Name: destino_pedido_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.destino_pedido_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.destino_pedido_seq OWNER TO postgres;

--
-- TOC entry 278 (class 1259 OID 124648)
-- Name: detalle_solicitud_material; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.detalle_solicitud_material (
    detalle_id integer NOT NULL,
    solicitud_id integer NOT NULL,
    material_id integer NOT NULL,
    cantidad_solicitada numeric(15,4) NOT NULL,
    cantidad_aprobada numeric(15,4)
);


ALTER TABLE public.detalle_solicitud_material OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 123834)
-- Name: detalle_solicitud_material_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.detalle_solicitud_material_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.detalle_solicitud_material_seq OWNER TO postgres;

--
-- TOC entry 275 (class 1259 OID 124606)
-- Name: evaluacion_final_proceso; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.evaluacion_final_proceso (
    evaluacion_id integer NOT NULL,
    lote_id integer NOT NULL,
    inspector_id integer,
    razon character varying(500),
    observaciones character varying(500),
    fecha_evaluacion timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.evaluacion_final_proceso OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 123831)
-- Name: evaluacion_final_proceso_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.evaluacion_final_proceso_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.evaluacion_final_proceso_seq OWNER TO postgres;

--
-- TOC entry 270 (class 1259 OID 124519)
-- Name: lote_materia_prima; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lote_materia_prima (
    lote_material_id integer NOT NULL,
    lote_id integer NOT NULL,
    materia_prima_id integer NOT NULL,
    cantidad_planificada numeric(15,4) NOT NULL,
    cantidad_usada numeric(15,4)
);


ALTER TABLE public.lote_materia_prima OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 123826)
-- Name: lote_materia_prima_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lote_materia_prima_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lote_materia_prima_seq OWNER TO postgres;

--
-- TOC entry 269 (class 1259 OID 124503)
-- Name: lote_produccion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lote_produccion (
    lote_id integer NOT NULL,
    pedido_id integer NOT NULL,
    codigo_lote character varying(50) NOT NULL,
    nombre character varying(100) DEFAULT 'Lote sin nombre'::character varying NOT NULL,
    fecha_creacion date DEFAULT CURRENT_DATE NOT NULL,
    hora_inicio timestamp(0) without time zone,
    hora_fin timestamp(0) without time zone,
    cantidad_objetivo numeric(15,4),
    cantidad_producida numeric(15,4),
    observaciones character varying(500)
);


ALTER TABLE public.lote_produccion OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 123825)
-- Name: lote_produccion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lote_produccion_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lote_produccion_seq OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 124354)
-- Name: maquina; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.maquina (
    maquina_id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion character varying(255),
    imagen_url character varying(500),
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.maquina OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 123815)
-- Name: maquina_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.maquina_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.maquina_seq OWNER TO postgres;

--
-- TOC entry 263 (class 1259 OID 124402)
-- Name: materia_prima; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.materia_prima (
    materia_prima_id integer NOT NULL,
    material_id integer NOT NULL,
    proveedor_id integer NOT NULL,
    lote_proveedor character varying(100),
    numero_factura character varying(100),
    fecha_recepcion date NOT NULL,
    fecha_vencimiento date,
    cantidad numeric(15,4) NOT NULL,
    cantidad_disponible numeric(15,4) NOT NULL,
    conformidad_recepcion boolean,
    observaciones character varying(500)
);


ALTER TABLE public.materia_prima OWNER TO postgres;

--
-- TOC entry 262 (class 1259 OID 124382)
-- Name: materia_prima_base; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.materia_prima_base (
    material_id integer NOT NULL,
    categoria_id integer NOT NULL,
    unidad_id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion character varying(255),
    cantidad_disponible numeric(15,4) DEFAULT '0'::numeric NOT NULL,
    stock_minimo numeric(15,4) DEFAULT '0'::numeric NOT NULL,
    stock_maximo numeric(15,4),
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.materia_prima_base OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 123818)
-- Name: materia_prima_base_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.materia_prima_base_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.materia_prima_base_seq OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 123819)
-- Name: materia_prima_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.materia_prima_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.materia_prima_seq OWNER TO postgres;

--
-- TOC entry 245 (class 1259 OID 124235)
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 124234)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- TOC entry 5220 (class 0 OID 0)
-- Dependencies: 244
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 250 (class 1259 OID 124263)
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 124274)
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO postgres;

--
-- TOC entry 261 (class 1259 OID 124372)
-- Name: operador; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.operador (
    operador_id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    apellido character varying(100) NOT NULL,
    usuario character varying(60) NOT NULL,
    password_hash character varying(255) NOT NULL,
    email character varying(100),
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.operador OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 123817)
-- Name: operador_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.operador_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.operador_seq OWNER TO postgres;

--
-- TOC entry 265 (class 1259 OID 124436)
-- Name: pedido_cliente; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pedido_cliente (
    pedido_id integer NOT NULL,
    cliente_id integer NOT NULL,
    numero_pedido character varying(50) NOT NULL,
    nombre character varying(200),
    cantidad numeric(15,4),
    estado character varying(50) DEFAULT 'pendiente'::character varying NOT NULL,
    fecha_creacion date DEFAULT CURRENT_DATE NOT NULL,
    fecha_entrega date,
    descripcion text,
    observaciones text,
    editable_hasta timestamp(0) without time zone,
    aprobado_en timestamp(0) without time zone,
    aprobado_por integer,
    razon_rechazo text,
    origen_sistema character varying(255),
    pedido_almacen_id bigint
);


ALTER TABLE public.pedido_cliente OWNER TO postgres;

--
-- TOC entry 5221 (class 0 OID 0)
-- Dependencies: 265
-- Name: COLUMN pedido_cliente.nombre; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.pedido_cliente.nombre IS 'Nombre del pedido';


--
-- TOC entry 5222 (class 0 OID 0)
-- Dependencies: 265
-- Name: COLUMN pedido_cliente.estado; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.pedido_cliente.estado IS 'pendiente, aprobado, rechazado, en_produccion, completado, cancelado';


--
-- TOC entry 5223 (class 0 OID 0)
-- Dependencies: 265
-- Name: COLUMN pedido_cliente.aprobado_por; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.pedido_cliente.aprobado_por IS 'ID del operador que aprobó';


--
-- TOC entry 5224 (class 0 OID 0)
-- Dependencies: 265
-- Name: COLUMN pedido_cliente.origen_sistema; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.pedido_cliente.origen_sistema IS 'Sistema de origen: almacen o null';


--
-- TOC entry 5225 (class 0 OID 0)
-- Dependencies: 265
-- Name: COLUMN pedido_cliente.pedido_almacen_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.pedido_cliente.pedido_almacen_id IS 'ID del pedido en sistema-almacen-PSIII';


--
-- TOC entry 229 (class 1259 OID 123821)
-- Name: pedido_cliente_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pedido_cliente_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pedido_cliente_seq OWNER TO postgres;

--
-- TOC entry 285 (class 1259 OID 132196)
-- Name: pedido_documentos_entrega; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pedido_documentos_entrega (
    id bigint NOT NULL,
    pedido_id character varying(50) NOT NULL,
    envio_id integer NOT NULL,
    envio_codigo character varying(255) NOT NULL,
    fecha_entrega timestamp(0) without time zone NOT NULL,
    transportista_nombre character varying(255),
    documentos json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.pedido_documentos_entrega OWNER TO postgres;

--
-- TOC entry 5226 (class 0 OID 0)
-- Dependencies: 285
-- Name: COLUMN pedido_documentos_entrega.pedido_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.pedido_documentos_entrega.pedido_id IS 'ID del pedido en Trazabilidad';


--
-- TOC entry 5227 (class 0 OID 0)
-- Dependencies: 285
-- Name: COLUMN pedido_documentos_entrega.envio_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.pedido_documentos_entrega.envio_id IS 'ID del envío en plantaCruds';


--
-- TOC entry 5228 (class 0 OID 0)
-- Dependencies: 285
-- Name: COLUMN pedido_documentos_entrega.envio_codigo; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.pedido_documentos_entrega.envio_codigo IS 'Código del envío';


--
-- TOC entry 5229 (class 0 OID 0)
-- Dependencies: 285
-- Name: COLUMN pedido_documentos_entrega.documentos; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.pedido_documentos_entrega.documentos IS 'Rutas de los documentos guardados';


--
-- TOC entry 284 (class 1259 OID 132195)
-- Name: pedido_documentos_entrega_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pedido_documentos_entrega_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pedido_documentos_entrega_id_seq OWNER TO postgres;

--
-- TOC entry 5230 (class 0 OID 0)
-- Dependencies: 284
-- Name: pedido_documentos_entrega_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pedido_documentos_entrega_id_seq OWNED BY public.pedido_documentos_entrega.id;


--
-- TOC entry 247 (class 1259 OID 124242)
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- TOC entry 246 (class 1259 OID 124241)
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO postgres;

--
-- TOC entry 5231 (class 0 OID 0)
-- Dependencies: 246
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- TOC entry 260 (class 1259 OID 124364)
-- Name: proceso; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.proceso (
    proceso_id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion character varying(255),
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.proceso OWNER TO postgres;

--
-- TOC entry 272 (class 1259 OID 124552)
-- Name: proceso_maquina; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.proceso_maquina (
    proceso_maquina_id integer NOT NULL,
    proceso_id integer NOT NULL,
    maquina_id integer NOT NULL,
    orden_paso integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion character varying(255),
    tiempo_estimado integer
);


ALTER TABLE public.proceso_maquina OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 123828)
-- Name: proceso_maquina_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.proceso_maquina_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.proceso_maquina_seq OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 123816)
-- Name: proceso_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.proceso_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.proceso_seq OWNER TO postgres;

--
-- TOC entry 264 (class 1259 OID 124419)
-- Name: producto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.producto (
    producto_id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(200) NOT NULL,
    tipo character varying(255) DEFAULT 'comestibles'::character varying NOT NULL,
    peso numeric(10,2),
    unidad_id integer,
    descripcion text,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    precio_unitario numeric(15,2),
    CONSTRAINT producto_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['organico'::character varying, 'marca_univalle'::character varying, 'comestibles'::character varying])::text[])))
);


ALTER TABLE public.producto OWNER TO postgres;

--
-- TOC entry 5232 (class 0 OID 0)
-- Dependencies: 264
-- Name: COLUMN producto.peso; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.producto.peso IS 'Peso en kg';


--
-- TOC entry 5233 (class 0 OID 0)
-- Dependencies: 264
-- Name: COLUMN producto.precio_unitario; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.producto.precio_unitario IS 'Precio unitario del producto';


--
-- TOC entry 268 (class 1259 OID 124485)
-- Name: producto_destino_pedido; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.producto_destino_pedido (
    producto_destino_id integer NOT NULL,
    destino_id integer NOT NULL,
    producto_pedido_id integer NOT NULL,
    cantidad numeric(15,4) NOT NULL,
    observaciones text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.producto_destino_pedido OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 123824)
-- Name: producto_destino_pedido_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.producto_destino_pedido_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.producto_destino_pedido_seq OWNER TO postgres;

--
-- TOC entry 266 (class 1259 OID 124453)
-- Name: producto_pedido; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.producto_pedido (
    producto_pedido_id integer NOT NULL,
    pedido_id integer NOT NULL,
    producto_id integer NOT NULL,
    cantidad numeric(15,4) NOT NULL,
    estado character varying(50) DEFAULT 'pendiente'::character varying NOT NULL,
    razon_rechazo text,
    aprobado_por integer,
    aprobado_en timestamp(0) without time zone,
    observaciones text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    precio numeric(15,2)
);


ALTER TABLE public.producto_pedido OWNER TO postgres;

--
-- TOC entry 5234 (class 0 OID 0)
-- Dependencies: 266
-- Name: COLUMN producto_pedido.estado; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.producto_pedido.estado IS 'pendiente, aprobado, rechazado';


--
-- TOC entry 5235 (class 0 OID 0)
-- Dependencies: 266
-- Name: COLUMN producto_pedido.precio; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.producto_pedido.precio IS 'Precio total (precio_unitario * cantidad)';


--
-- TOC entry 230 (class 1259 OID 123822)
-- Name: producto_pedido_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.producto_pedido_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.producto_pedido_seq OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 123820)
-- Name: producto_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.producto_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.producto_seq OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 124336)
-- Name: proveedor; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.proveedor (
    proveedor_id integer NOT NULL,
    razon_social character varying(200) NOT NULL,
    nombre_comercial character varying(200),
    nit character varying(20),
    contacto character varying(100),
    telefono character varying(20),
    email character varying(100),
    direccion character varying(255),
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.proveedor OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 123813)
-- Name: proveedor_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.proveedor_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.proveedor_seq OWNER TO postgres;

--
-- TOC entry 271 (class 1259 OID 124534)
-- Name: registro_movimiento_material; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.registro_movimiento_material (
    registro_id integer NOT NULL,
    material_id integer NOT NULL,
    tipo_movimiento_id integer NOT NULL,
    operador_id integer,
    cantidad numeric(15,4) NOT NULL,
    saldo_anterior numeric(15,4),
    saldo_nuevo numeric(15,4),
    descripcion character varying(500),
    observaciones text,
    fecha_movimiento timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.registro_movimiento_material OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 123827)
-- Name: registro_movimiento_material_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.registro_movimiento_material_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.registro_movimiento_material_seq OWNER TO postgres;

--
-- TOC entry 274 (class 1259 OID 124583)
-- Name: registro_proceso_maquina; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.registro_proceso_maquina (
    registro_id integer NOT NULL,
    lote_id integer NOT NULL,
    proceso_maquina_id integer NOT NULL,
    operador_id integer NOT NULL,
    variables_ingresadas text NOT NULL,
    cumple_estandar boolean NOT NULL,
    observaciones character varying(500),
    hora_inicio timestamp(0) without time zone,
    hora_fin timestamp(0) without time zone,
    fecha_registro timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.registro_proceso_maquina OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 123830)
-- Name: registro_proceso_maquina_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.registro_proceso_maquina_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.registro_proceso_maquina_seq OWNER TO postgres;

--
-- TOC entry 279 (class 1259 OID 124663)
-- Name: respuesta_proveedor; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.respuesta_proveedor (
    respuesta_id integer NOT NULL,
    solicitud_id integer NOT NULL,
    proveedor_id integer NOT NULL,
    fecha_respuesta timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    cantidad_confirmada numeric(15,4),
    fecha_entrega date,
    observaciones text,
    precio numeric(15,2)
);


ALTER TABLE public.respuesta_proveedor OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 123835)
-- Name: respuesta_proveedor_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.respuesta_proveedor_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.respuesta_proveedor_seq OWNER TO postgres;

--
-- TOC entry 252 (class 1259 OID 124285)
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO postgres;

--
-- TOC entry 249 (class 1259 OID 124253)
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- TOC entry 248 (class 1259 OID 124252)
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- TOC entry 5236 (class 0 OID 0)
-- Dependencies: 248
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- TOC entry 281 (class 1259 OID 124682)
-- Name: seguimiento_envio_pedido; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.seguimiento_envio_pedido (
    id bigint NOT NULL,
    pedido_id integer,
    destino_id integer,
    envio_id integer,
    codigo_envio character varying(255),
    estado character varying(255) DEFAULT 'pendiente'::character varying NOT NULL,
    mensaje_error text,
    datos_solicitud json,
    datos_respuesta json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.seguimiento_envio_pedido OWNER TO postgres;

--
-- TOC entry 280 (class 1259 OID 124681)
-- Name: seguimiento_envio_pedido_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seguimiento_envio_pedido_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.seguimiento_envio_pedido_id_seq OWNER TO postgres;

--
-- TOC entry 5237 (class 0 OID 0)
-- Dependencies: 280
-- Name: seguimiento_envio_pedido_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.seguimiento_envio_pedido_id_seq OWNED BY public.seguimiento_envio_pedido.id;


--
-- TOC entry 277 (class 1259 OID 124632)
-- Name: solicitud_material; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.solicitud_material (
    solicitud_id integer NOT NULL,
    pedido_id integer NOT NULL,
    numero_solicitud character varying(50) NOT NULL,
    fecha_solicitud date DEFAULT CURRENT_DATE NOT NULL,
    fecha_requerida date NOT NULL,
    observaciones text,
    direccion character varying(500),
    latitud numeric(10,8),
    longitud numeric(11,8)
);


ALTER TABLE public.solicitud_material OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 123833)
-- Name: solicitud_material_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.solicitud_material_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.solicitud_material_seq OWNER TO postgres;

--
-- TOC entry 254 (class 1259 OID 124308)
-- Name: tipo_movimiento; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipo_movimiento (
    tipo_movimiento_id integer NOT NULL,
    codigo character varying(20) NOT NULL,
    nombre character varying(100) NOT NULL,
    afecta_stock boolean DEFAULT true NOT NULL,
    es_entrada boolean DEFAULT false NOT NULL,
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.tipo_movimiento OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 123810)
-- Name: tipo_movimiento_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipo_movimiento_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipo_movimiento_seq OWNER TO postgres;

--
-- TOC entry 253 (class 1259 OID 124300)
-- Name: unidad_medida; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.unidad_medida (
    unidad_id integer NOT NULL,
    codigo character varying(10) NOT NULL,
    nombre character varying(50) NOT NULL,
    descripcion character varying(255),
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.unidad_medida OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 123809)
-- Name: unidad_medida_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.unidad_medida_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.unidad_medida_seq OWNER TO postgres;

--
-- TOC entry 258 (class 1259 OID 124346)
-- Name: variable_estandar; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.variable_estandar (
    variable_id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(100) NOT NULL,
    unidad character varying(50),
    descripcion character varying(255),
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.variable_estandar OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 123814)
-- Name: variable_estandar_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.variable_estandar_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.variable_estandar_seq OWNER TO postgres;

--
-- TOC entry 273 (class 1259 OID 124567)
-- Name: variable_proceso_maquina; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.variable_proceso_maquina (
    variable_id integer NOT NULL,
    proceso_maquina_id integer NOT NULL,
    variable_estandar_id integer NOT NULL,
    valor_minimo numeric(10,2) NOT NULL,
    valor_maximo numeric(10,2) NOT NULL,
    valor_objetivo numeric(10,2),
    obligatorio boolean DEFAULT true NOT NULL
);


ALTER TABLE public.variable_proceso_maquina OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 123829)
-- Name: variable_proceso_maquina_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.variable_proceso_maquina_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.variable_proceso_maquina_seq OWNER TO postgres;

--
-- TOC entry 4816 (class 2604 OID 124238)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 4849 (class 2604 OID 132199)
-- Name: pedido_documentos_entrega id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pedido_documentos_entrega ALTER COLUMN id SET DEFAULT nextval('public.pedido_documentos_entrega_id_seq'::regclass);


--
-- TOC entry 4817 (class 2604 OID 124245)
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- TOC entry 4818 (class 2604 OID 124256)
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- TOC entry 4847 (class 2604 OID 124685)
-- Name: seguimiento_envio_pedido id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_envio_pedido ALTER COLUMN id SET DEFAULT nextval('public.seguimiento_envio_pedido_id_seq'::regclass);


--
-- TOC entry 5204 (class 0 OID 124619)
-- Dependencies: 276
-- Data for Name: almacenaje; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.almacenaje (almacenaje_id, lote_id, ubicacion, condicion, cantidad, observaciones, latitud_recojo, longitud_recojo, direccion_recojo, referencia_recojo, fecha_almacenaje, fecha_retiro) FROM stdin;
1	1	Almacén Principal	seco	100.0000	no	-17.79365118	-63.17851067	Calle H. Salazar, 359, Centro, Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-10 05:56:06	\N
2	2	Almacén Principal	seco	15.0000	no	-17.79907558	-63.16821098	Avenida Perimetral, Estación Argentina, Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-10 06:03:11	\N
3	3	Almacén Principal	seco	100.0000	no	-17.79897854	-63.18709373	Calle Héroes del Chaco, El Pari, Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-10 06:27:21	\N
4	4	Almacén Principal	seco	100.0000	no	-17.78765771	-63.17374572	Calle Toledo Pimentel, Centro, Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-10 20:27:51	\N
5	5	Almacén Principal	seco	1000.0000	no	-17.86111526	-63.11473597	Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-11 14:42:35	\N
6	7	Almacén Principal	seco	100.0000	no	-17.80064061	-63.16347647	Calle Campo Rosa del Sara, Estación Argentina, Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-11 15:03:11	\N
7	6	Almacén Principal	seco	100.0000	no	-17.81419606	-63.16743896	Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-12 20:34:20	\N
8	8	Almacén Principal	seco	100.0000	no	-17.83800324	-63.16471858	Nuevo Palmar, Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-12 21:23:08	\N
9	9	Almacén Principal	seco	100.0000	NO	-17.78942410	-63.17243653	Calle Oruro, 621, Centro, Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-12 21:47:33	\N
10	10	Almacén Principal	seco	100.0000	no	-17.79802696	-63.16313335	Calle Campo Rosa del Sara, Estación Argentina, Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-13 03:52:15	\N
11	11	Almacén Principal	seco	100.0000	no	-17.79264923	-63.16793288	Calle Teniente Roca Peirano, Estación Argentina, Santa Cruz de la Sierra, Santa Cruz, Bolivia	casa rosada	2025-12-13 04:12:04	\N
12	12	Almacén Principal	seco	10.0000	no	-17.81460000	-63.15610000	Av. Ejemplo 123, Santa Cruz de la Sierra, Bolivia	\N	2025-12-14 13:08:52	\N
\.


--
-- TOC entry 5210 (class 0 OID 124694)
-- Dependencies: 282
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-spatie.permission.cache	a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:21:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:17:"ver panel control";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:17:"ver panel cliente";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:13:"crear pedidos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:15:"ver mis pedidos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:18:"editar mis pedidos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:20:"cancelar mis pedidos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:3;}}i:6;a:4:{s:1:"a";i:7;s:1:"b";s:17:"gestionar pedidos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:7;a:4:{s:1:"a";i:8;s:1:"b";s:15:"aprobar pedidos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:8;a:4:{s:1:"a";i:9;s:1:"b";s:16:"rechazar pedidos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:9;a:4:{s:1:"a";i:10;s:1:"b";s:17:"ver materia prima";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:10;a:4:{s:1:"a";i:11;s:1:"b";s:23:"solicitar materia prima";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:11;a:4:{s:1:"a";i:12;s:1:"b";s:25:"recepcionar materia prima";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:12;a:4:{s:1:"a";i:13;s:1:"b";s:21:"gestionar proveedores";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:15:"gestionar lotes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:18:"gestionar maquinas";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:18:"gestionar procesos";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:28:"gestionar variables estandar";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:16:"certificar lotes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:18;a:4:{s:1:"a";i:19;s:1:"b";s:16:"ver certificados";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:15:"almacenar lotes";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:2;i:1;i:3;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:18:"gestionar usuarios";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:3;}}}s:5:"roles";a:3:{i:0;a:3:{s:1:"a";i:2;s:1:"b";s:8:"operador";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:3;s:1:"b";s:5:"admin";s:1:"c";s:3:"web";}i:2;a:3:{s:1:"a";i:1;s:1:"b";s:7:"cliente";s:1:"c";s:3:"web";}}}	1765772341
laravel-cache-almacenes_planta_cruds	a:1:{i:0;a:9:{s:2:"id";i:1;s:6:"nombre";s:14:"Planta Central";s:9:"direccion";s:31:"Av. Cristo Redentor, Santa Cruz";s:7:"latitud";s:11:"-17.7833000";s:8:"longitud";s:11:"-63.1821000";s:6:"activo";b:1;s:9:"es_planta";b:1;s:18:"usuario_almacen_id";i:1;s:15:"usuario_almacen";a:3:{s:2:"id";i:1;s:4:"name";s:5:"Admin";s:5:"email";s:18:"admin@orgtrack.com";}}}	1765723929
\.


--
-- TOC entry 5211 (class 0 OID 124701)
-- Dependencies: 283
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- TOC entry 5184 (class 0 OID 124328)
-- Dependencies: 256
-- Data for Name: categoria_materia_prima; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categoria_materia_prima (categoria_id, codigo, nombre, descripcion, activo) FROM stdin;
1	CAT-GRANOS	Granos y Cereales	Trigo, maíz, arroz, avena, cebada, etc.	t
2	CAT-LACTEOS	Lácteos y Derivados	Leche, queso, mantequilla, yogurt, suero, etc.	t
3	CAT-CARNICOS	Cárnicos y Embutidos	Carne de res, cerdo, pollo, pescado, embutidos.	t
4	CAT-FRUTAS	Frutas y Verduras	Frutas frescas, verduras, hortalizas, tubérculos.	t
5	CAT-ACEITES	Aceites y Grasas	Aceite vegetal, manteca, margarina, grasas animales.	t
6	CAT-ESPECIAS	Especias y Condimentos	Sal, pimienta, orégano, comino, salsas, vinagres.	t
7	CAT-AZUCARES	Azúcares y Edulcorantes	Azúcar blanca, morena, miel, jarabes, edulcorantes.	t
8	CAT-ADITIVOS	Aditivos Alimentarios	Conservantes, colorantes, saborizantes, estabilizantes.	t
9	CAT-EMPAQUES	Empaques y Embalajes	Bolsas, cajas, etiquetas, frascos, tapas.	t
10	CAT-OTROS	Otros Insumos	Insumos que no encajan en las categorías anteriores.	t
\.


--
-- TOC entry 5183 (class 0 OID 124318)
-- Dependencies: 255
-- Data for Name: cliente; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cliente (cliente_id, razon_social, nombre_comercial, nit, direccion, telefono, email, contacto, activo) FROM stdin;
1	jhair aguilar	jhair aguilar	\N	\N	\N	jhair@gmail.com	jhair aguilar	t
2	jhair cliente aguilar	jhair cliente aguilar	\N	\N	\N	jhairaguilar483.est@gmail.com	jhair cliente aguilar	t
3	Cliente test@example.com	Cliente test@example.com	\N	\N	\N	test@example.com	Cliente test@example.com	t
4	Juan Pérez	Juan Pérez	123456789	Av. Principal 123, Santa Cruz, Bolivia	+591 70000000	cliente@example.com	Juan Pérez	t
\.


--
-- TOC entry 5195 (class 0 OID 124472)
-- Dependencies: 267
-- Data for Name: destino_pedido; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.destino_pedido (destino_id, pedido_id, direccion, referencia, latitud, longitud, nombre_contacto, telefono_contacto, instrucciones_entrega, almacen_origen_id, almacen_origen_nombre, almacen_destino_id, almacen_destino_nombre, created_at, updated_at, almacen_almacen_id) FROM stdin;
1	1	ALAMACEN 1	ninguna	-17.80933144	-63.19550514	jhair cliente aguilar	78113449	NO	\N	\N	\N	\N	2025-12-10 04:57:13	2025-12-10 04:57:13	\N
2	1	ALAMACEN 2	dsa	-17.82600106	-63.19018364	jhair cliente aguilar	78113449	NO	\N	\N	\N	\N	2025-12-10 04:57:13	2025-12-10 04:57:13	\N
3	1	almacen 3	dsa	-17.81472473	-63.16718102	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-10 04:57:13	2025-12-10 04:57:13	\N
5	2	alm1	ninguna	-17.81635902	-63.19104195	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-10 06:00:17	2025-12-10 06:00:17	\N
7	3	almacen 1	ninguna	-17.80328423	-63.18915367	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-10 06:20:58	2025-12-10 06:20:58	\N
8	3	almacen 2	ninguna	-17.81063892	-63.18554878	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-10 06:20:58	2025-12-10 06:20:58	\N
10	4	ALMACEN 1	NO	-17.81672220	-63.14982596	jhair cliente aguilar	78113449	NO	\N	\N	\N	\N	2025-12-10 19:47:23	2025-12-10 19:47:23	\N
12	5	almacen 1	NO	-17.80152270	-63.16115233	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-11 01:11:11	2025-12-11 01:11:11	\N
13	6	Av. Test 123	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	2025-12-11 01:47:15	2025-12-11 01:47:15	\N
18	8	almacen	NO	-17.80920433	-63.18054444	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-11 02:22:54	2025-12-11 02:22:54	\N
19	8	almacen 1	ninguna	-17.79694625	-63.14896791	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-11 02:22:55	2025-12-11 02:22:55	\N
20	7	Av. Ejemplo 123, Santa Cruz, Bolivia	Frente al parque central, edificio azul	-17.81460000	-63.15610000	Juan Pérez	+591 70000000	Entregar en horario de oficina (8:00 AM - 6:00 PM). Llamar antes de llegar.	\N	\N	\N	\N	2025-12-11 02:52:27	2025-12-11 02:52:27	\N
21	7	Calle Segunda 456, La Paz, Bolivia	Edificio azul, piso 3, oficina 301	-16.50000000	-68.15000000	María González	+591 70111111	Llamar antes de entregar. Recepción en planta baja.	\N	\N	\N	\N	2025-12-11 02:52:27	2025-12-11 02:52:27	\N
22	7	Av. Tercera 789, Cochabamba, Bolivia	Cerca del mercado central	-17.39350000	-66.15700000	Carlos Rodríguez	+591 70222222	Entregar en la mañana preferentemente	\N	\N	\N	\N	2025-12-11 02:52:27	2025-12-11 02:52:27	\N
23	9	no	NO	-17.82881550	-63.15548915	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-11 15:00:02	2025-12-11 15:00:02	\N
24	10	Av. Ejemplo 123, Santa Cruz, Bolivia	Frente al parque central, edificio azul	-17.81460000	-63.15610000	Juan Pérez	+591 70000000	Entregar en horario de oficina (8:00 AM - 6:00 PM). Llamar antes de llegar.	\N	\N	\N	\N	2025-12-12 19:54:47	2025-12-12 19:54:47	\N
25	10	Calle Segunda 456, La Paz, Bolivia	Edificio azul, piso 3, oficina 301	-16.50000000	-68.15000000	María González	+591 70111111	Llamar antes de entregar. Recepción en planta baja.	\N	\N	\N	\N	2025-12-12 19:54:47	2025-12-12 19:54:47	\N
26	10	Av. Tercera 789, Cochabamba, Bolivia	Cerca del mercado central	-17.39350000	-66.15700000	Carlos Rodríguez	+591 70222222	Entregar en la mañana preferentemente	\N	\N	\N	\N	2025-12-12 19:54:47	2025-12-12 19:54:47	\N
28	11	ALMACEN 1	NO	-17.80511839	-63.19084113	jhair cliente aguilar	78113449	NO	\N	\N	\N	\N	2025-12-12 21:43:05	2025-12-12 21:43:05	\N
30	12	almacen1	NO	-17.80086893	-63.16355489	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-13 03:49:56	2025-12-13 03:49:56	\N
32	13	almacen	NO	-17.80217646	-63.18071605	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-13 04:08:28	2025-12-13 04:08:28	\N
34	14	Almacen 1	NO	-17.80838715	-63.17539609	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-14 12:03:52	2025-12-14 12:03:52	\N
36	15	alm1	NO	-17.81132897	-63.14793824	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-14 12:10:13	2025-12-14 12:10:13	\N
38	16	alm1	NO	-17.80822371	-63.17797027	jhair cliente aguilar	78113449	no	\N	\N	\N	\N	2025-12-14 13:52:40	2025-12-14 13:52:40	\N
\.


--
-- TOC entry 5206 (class 0 OID 124648)
-- Dependencies: 278
-- Data for Name: detalle_solicitud_material; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.detalle_solicitud_material (detalle_id, solicitud_id, material_id, cantidad_solicitada, cantidad_aprobada) FROM stdin;
1	1	1	100.0000	100.0000
2	2	2	200.0000	200.0000
3	3	1	100.0000	100.0000
4	4	1	500.0000	500.0000
5	5	2	200.0000	200.0000
6	6	2	100.0000	100.0000
7	7	1	100.0000	100.0000
8	8	1	100.0000	100.0000
9	9	1	100.0000	100.0000
10	10	1	100.0000	100.0000
11	11	3	50.0000	50.0000
12	12	1	100.0000	100.0000
\.


--
-- TOC entry 5203 (class 0 OID 124606)
-- Dependencies: 275
-- Data for Name: evaluacion_final_proceso; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.evaluacion_final_proceso (evaluacion_id, lote_id, inspector_id, razon, observaciones, fecha_evaluacion) FROM stdin;
1	1	1	Todas las máquinas cumplen los valores estándar	\N	2025-12-10 05:49:25
2	2	1	Todas las máquinas cumplen los valores estándar	no	2025-12-10 06:02:58
3	3	1	Todas las máquinas cumplen los valores estándar	ok	2025-12-10 06:25:30
4	4	1	Todas las máquinas cumplen los valores estándar	\N	2025-12-10 19:49:58
5	5	1	Todas las máquinas cumplen los valores estándar	\N	2025-12-11 14:24:05
6	6	1	Todas las máquinas cumplen los valores estándar	\N	2025-12-11 14:54:50
7	7	1	Todas las máquinas cumplen los valores estándar	\N	2025-12-11 15:02:00
8	8	1	Todas las máquinas cumplen los valores estándar	\N	2025-12-12 21:22:34
9	9	1	Todas las máquinas cumplen los valores estándar	\N	2025-12-12 21:47:08
10	10	1	Todas las máquinas cumplen los valores estándar	\N	2025-12-13 03:51:53
11	11	1	Todas las máquinas cumplen los valores estándar	\N	2025-12-13 04:11:44
12	12	1	Todas las máquinas cumplen los valores estándar	\N	2025-12-14 13:08:12
\.


--
-- TOC entry 5198 (class 0 OID 124519)
-- Dependencies: 270
-- Data for Name: lote_materia_prima; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lote_materia_prima (lote_material_id, lote_id, materia_prima_id, cantidad_planificada, cantidad_usada) FROM stdin;
1	1	1	100.0000	0.0000
2	2	2	150.0000	0.0000
3	3	3	100.0000	0.0000
4	3	2	50.0000	0.0000
5	4	4	100.0000	0.0000
6	5	5	100.0000	0.0000
7	6	5	100.0000	0.0000
8	7	4	100.0000	0.0000
9	8	8	100.0000	0.0000
10	9	4	100.0000	0.0000
11	10	4	100.0000	0.0000
12	11	4	100.0000	0.0000
13	12	7	10.0000	0.0000
\.


--
-- TOC entry 5197 (class 0 OID 124503)
-- Dependencies: 269
-- Data for Name: lote_produccion; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lote_produccion (lote_id, pedido_id, codigo_lote, nombre, fecha_creacion, hora_inicio, hora_fin, cantidad_objetivo, cantidad_producida, observaciones) FROM stdin;
1	1	LOTE-0001-20251210	moledora 1	2025-12-10	\N	\N	100.0000	\N	no
2	2	LOTE-0002-20251210	miel	2025-12-10	\N	\N	15.0000	\N	miel
3	3	LOTE-0003-20251210	pedido 3	2025-12-10	\N	\N	100.0000	\N	no
4	4	LOTE-0004-20251210	pedido 4	2025-12-10	\N	\N	100.0000	\N	no
5	8	LOTE-0005-20251211	pedido 5	2025-12-11	\N	\N	1000.0000	\N	\N
6	7	LOTE-0006-20251211	pedido completo	2025-12-11	\N	\N	100.0000	\N	no
7	9	LOTE-0007-20251211	pedido 6	2025-12-11	\N	\N	100.0000	\N	no
8	5	LOTE-0008-20251212	pedido jhair	2025-12-12	\N	\N	100.0000	\N	no
9	11	LOTE-0009-20251212	pedido 7	2025-12-12	\N	\N	100.0000	\N	NO
10	12	LOTE-0010-20251213	pedido 8	2025-12-13	\N	\N	100.0000	\N	no
11	13	LOTE-0011-20251213	pedido 9	2025-12-13	\N	\N	100.0000	\N	\N
12	15	LOTE-0012-20251214	pedido 11	2025-12-14	\N	\N	10.0000	\N	no
\.


--
-- TOC entry 5187 (class 0 OID 124354)
-- Dependencies: 259
-- Data for Name: maquina; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.maquina (maquina_id, codigo, nombre, descripcion, imagen_url, activo) FROM stdin;
1	MEZCLADORA_01	Mezcladora Principal	Mezcladora de alta capacidad para materias primas	\N	t
2	EXTRUSORA_01	Extrusora de Plástico	Máquina extrusora para procesamiento de plástico	\N	t
3	HORNO_01	Horno de Secado	Horno para secado y tratamiento térmico	\N	t
4	ENVASADORA_01	Envasadora Automática	Máquina automática para envasado de productos	\N	t
5	ETIQUETADORA_01	Etiquetadora	Máquina para etiquetado de productos	\N	t
6	EMPAQUETADORA_01	Empaquetadora	Máquina para empaquetado final de productos	\N	t
7	MOLINO_01	Molino de Martillos	Molino para trituración de materiales	\N	t
8	TAMIZADORA_01	Tamizadora	Máquina para tamizado y clasificación	https://res.cloudinary.com/dtnweo7db/image/upload/v1765344663/maquinas/vsuh9qcjnwxuelcnroto.jpg	t
\.


--
-- TOC entry 5191 (class 0 OID 124402)
-- Dependencies: 263
-- Data for Name: materia_prima; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.materia_prima (materia_prima_id, material_id, proveedor_id, lote_proveedor, numero_factura, fecha_recepcion, fecha_vencimiento, cantidad, cantidad_disponible, conformidad_recepcion, observaciones) FROM stdin;
1	1	1	\N	123456789	2025-12-10	\N	100.0000	0.0000	t	no
3	1	1	\N	2345678	2025-12-10	\N	100.0000	0.0000	t	no
2	2	1	\N	123456789	2025-12-10	\N	200.0000	0.0000	t	miel
6	2	1	\N	2345678	2025-12-11	\N	100.0000	100.0000	t	\N
5	2	1	\N	2345678	2025-12-11	\N	200.0000	0.0000	t	\N
8	1	1	\N	2345678	2025-12-12	\N	100.0000	0.0000	t	no
9	1	1	\N	12345678	2025-12-15	\N	100.0000	100.0000	t	\N
10	1	1	\N	12345	2025-12-17	\N	100.0000	100.0000	t	no
4	1	1	\N	2345678	2025-12-13	\N	500.0000	0.0000	t	no
11	3	1	\N	2345678	2025-12-15	\N	50.0000	50.0000	t	\N
7	1	1	\N	2345678	2025-12-14	\N	100.0000	90.0000	t	\N
12	1	1	\N	2345678	2025-12-14	\N	100.0000	100.0000	t	no
\.


--
-- TOC entry 5190 (class 0 OID 124382)
-- Dependencies: 262
-- Data for Name: materia_prima_base; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.materia_prima_base (material_id, categoria_id, unidad_id, codigo, nombre, descripcion, cantidad_disponible, stock_minimo, stock_maximo, activo) FROM stdin;
2	7	3	MP-0002	miel	miel	100.0000	0.0000	1000.0000	t
3	4	1	MP-0003	papa	no	50.0000	0.0000	1000.0000	t
1	1	1	MP-0001	harina	no	390.0000	0.0000	1000.0000	t
\.


--
-- TOC entry 5173 (class 0 OID 124235)
-- Dependencies: 245
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2025_01_19_000000_create_spatie_permission_tables	1
2	2025_01_20_000000_create_database_espanol_sin_bucles	1
3	2025_01_20_000001_migrate_data_to_spanish_tables	1
4	2025_09_08_174247_create_cache_table	1
5	2025_01_21_000000_add_precio_to_productos_and_pedidos	2
6	2025_01_22_000000_remove_prioridad_from_tables	3
7	2025_12_13_054435_add_origen_fields_to_pedido_cliente_table	4
8	2025_12_13_064948_add_almacen_almacen_id_to_destino_pedido	4
9	2025_12_14_025905_create_pedido_documentos_entrega_table	4
10	2025_12_14_133901_add_direccion_to_solicitud_material_table	5
\.


--
-- TOC entry 5178 (class 0 OID 124263)
-- Dependencies: 250
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- TOC entry 5179 (class 0 OID 124274)
-- Dependencies: 251
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
3	App\\Models\\Operator	1
1	App\\Models\\Operator	3
2	App\\Models\\Operator	4
\.


--
-- TOC entry 5189 (class 0 OID 124372)
-- Dependencies: 261
-- Data for Name: operador; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.operador (operador_id, nombre, apellido, usuario, password_hash, email, activo) FROM stdin;
1	jhair	aguilar	jhair	$2y$12$PsWcWtGV3nuBopkEusDKFup5.T5/FrHW0jeUV2ElAjeRJMa7Jgczq	jhair@gmail.com	t
3	jhair cliente	aguilar	jhaircliente	$2y$12$qoQw8PoBy3FV9ZSH9IS8AuiLXf3IfPsC6D1QHk5AdpwcnBip9tkAO	jhairaguilar483.est@gmail.com	t
4	jhair operador	aguilar operador	jhairoperador	$2y$12$HnVM3jYMCnADs.tM1QW38uRDd9trz2PhRLiWfrzQpU1CJxI3Kn9GW	jhairaguilar.est@gmail.com	t
\.


--
-- TOC entry 5193 (class 0 OID 124436)
-- Dependencies: 265
-- Data for Name: pedido_cliente; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pedido_cliente (pedido_id, cliente_id, numero_pedido, nombre, cantidad, estado, fecha_creacion, fecha_entrega, descripcion, observaciones, editable_hasta, aprobado_en, aprobado_por, razon_rechazo, origen_sistema, pedido_almacen_id) FROM stdin;
1	1	PED-0001-20251210	pedido 1	\N	aprobado	2025-12-10	2025-12-13	no	\N	2025-12-11 04:29:40	2025-12-10 04:57:27	1	\N	\N	\N
2	1	PED-0002-20251210	pedido 2	\N	aprobado	2025-12-10	2025-12-14	no	\N	2025-12-11 06:00:17	2025-12-10 06:00:30	1	\N	\N	\N
3	1	PED-0003-20251210	pedido 3	\N	almacenado	2025-12-10	2025-12-14	no	\N	2025-12-11 06:20:58	2025-12-10 06:21:20	1	\N	\N	\N
4	1	PED-0004-20251210	pedido 4	\N	almacenado	2025-12-10	2025-12-18	no	\N	2025-12-11 19:47:23	2025-12-10 19:47:39	1	\N	\N	\N
6	3	PED-0006-20251211	Pedido Test	\N	pendiente	2025-12-11	\N	\N	\N	2025-12-12 01:47:15	\N	\N	\N	\N	\N
8	1	PED-0008-20251211	pedido 5	\N	almacenado	2025-12-11	2025-12-17	no	\N	2025-12-12 02:22:54	2025-12-11 02:24:15	1	\N	\N	\N
9	1	PED-0009-20251211	pedido 6	\N	almacenado	2025-12-11	2025-12-17	\N	\N	2025-12-12 14:58:38	2025-12-11 15:00:15	1	\N	\N	\N
10	4	PED-0010-20251212	Pedido Completo - Enero 2025	\N	pendiente	2025-12-12	2025-02-15	Descripción completa del pedido con todos los detalles necesarios	Observaciones adicionales del pedido. Este es un pedido de prueba con todos los campos completos.	2025-12-31 23:59:59	\N	\N	\N	\N	\N
7	4	PED-0007-20251211	Pedido Completo - Enero 202522	\N	almacenado	2025-12-11	2025-02-15	Descripción completa del pedido con todos los detalles necesarios	\N	2025-12-31 23:59:59	2025-12-11 14:52:02	1	\N	\N	\N
5	2	PED-0005-20251211	pedido jhair	\N	almacenado	2025-12-11	2025-12-17	no	\N	2025-12-12 01:11:11	2025-12-12 21:21:15	1	\N	\N	\N
11	1	PED-0011-20251212	pedido 7	\N	almacenado	2025-12-12	2025-12-17	NO	\N	2025-12-13 21:43:05	2025-12-12 21:44:14	1	\N	\N	\N
12	1	PED-0012-20251213	pedido 8	\N	almacenado	2025-12-13	2025-12-16	no	\N	2025-12-14 03:49:56	2025-12-13 03:50:13	1	\N	\N	\N
13	1	PED-0013-20251213	pedido 9	\N	almacenado	2025-12-13	2025-12-17	\N	\N	2025-12-14 04:08:28	2025-12-13 04:08:47	1	\N	\N	\N
14	1	PED-0014-20251214	pedido 10	\N	rechazado	2025-12-14	2025-12-17	no	\N	2025-12-15 12:03:52	2025-12-14 12:09:13	1	no se puede	\N	\N
15	1	PED-0015-20251214	pedido 11	\N	almacenado	2025-12-14	2025-12-17	\N	\N	2025-12-15 12:10:13	2025-12-14 12:10:29	1	\N	\N	\N
16	1	PED-0016-20251214	pedido 12	\N	pendiente	2025-12-14	2025-12-19	\N	\N	2025-12-15 13:52:40	\N	\N	\N	\N	\N
\.


--
-- TOC entry 5213 (class 0 OID 132196)
-- Dependencies: 285
-- Data for Name: pedido_documentos_entrega; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pedido_documentos_entrega (id, pedido_id, envio_id, envio_codigo, fecha_entrega, transportista_nombre, documentos, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5175 (class 0 OID 124242)
-- Dependencies: 247
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
1	ver panel control	web	2025-12-09 18:27:56	2025-12-09 18:27:56
2	ver panel cliente	web	2025-12-09 18:27:56	2025-12-09 18:27:56
3	crear pedidos	web	2025-12-09 18:27:56	2025-12-09 18:27:56
4	ver mis pedidos	web	2025-12-09 18:27:56	2025-12-09 18:27:56
5	editar mis pedidos	web	2025-12-09 18:27:56	2025-12-09 18:27:56
6	cancelar mis pedidos	web	2025-12-09 18:27:56	2025-12-09 18:27:56
7	gestionar pedidos	web	2025-12-09 18:27:56	2025-12-09 18:27:56
8	aprobar pedidos	web	2025-12-09 18:27:56	2025-12-09 18:27:56
9	rechazar pedidos	web	2025-12-09 18:27:56	2025-12-09 18:27:56
10	ver materia prima	web	2025-12-09 18:27:56	2025-12-09 18:27:56
11	solicitar materia prima	web	2025-12-09 18:27:56	2025-12-09 18:27:56
12	recepcionar materia prima	web	2025-12-09 18:27:56	2025-12-09 18:27:56
13	gestionar proveedores	web	2025-12-09 18:27:56	2025-12-09 18:27:56
14	gestionar lotes	web	2025-12-09 18:27:56	2025-12-09 18:27:56
15	gestionar maquinas	web	2025-12-09 18:27:56	2025-12-09 18:27:56
16	gestionar procesos	web	2025-12-09 18:27:56	2025-12-09 18:27:56
17	gestionar variables estandar	web	2025-12-09 18:27:56	2025-12-09 18:27:56
18	certificar lotes	web	2025-12-09 18:27:56	2025-12-09 18:27:56
19	ver certificados	web	2025-12-09 18:27:56	2025-12-09 18:27:56
20	almacenar lotes	web	2025-12-09 18:27:56	2025-12-09 18:27:56
21	gestionar usuarios	web	2025-12-09 18:27:56	2025-12-09 18:27:56
\.


--
-- TOC entry 5188 (class 0 OID 124364)
-- Dependencies: 260
-- Data for Name: proceso; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.proceso (proceso_id, codigo, nombre, descripcion, activo) FROM stdin;
1	PREPARACION	Preparación de Materias Primas	Proceso de preparación y mezcla inicial de materias primas	t
2	MEZCLADO	Mezclado	Proceso de mezclado de componentes	t
3	EXTRUSION	Extrusión	Proceso de extrusión del material	t
4	MOLDEO	Moldeo	Proceso de moldeo del producto	t
5	SECADO	Secado	Proceso de secado del producto	t
6	TRATAMIENTO	Tratamiento Térmico	Proceso de tratamiento térmico	t
7	ENVASADO	Envasado	Proceso de envasado del producto final	t
8	ETIQUETADO	Etiquetado	Proceso de etiquetado de productos	t
9	EMPAQUETADO	Empaquetado	Proceso de empaquetado final	t
10	CONTROL_CALIDAD	Control de Calidad	Proceso de inspección y control de calidad	t
\.


--
-- TOC entry 5200 (class 0 OID 124552)
-- Dependencies: 272
-- Data for Name: proceso_maquina; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.proceso_maquina (proceso_maquina_id, proceso_id, maquina_id, orden_paso, nombre, descripcion, tiempo_estimado) FROM stdin;
1	10	8	1	Tamizadora	nuevo proceso	10
2	9	1	1	Mezcladora Principal	mezcar	5
3	9	7	2	Molino de Martillos	moler	10
\.


--
-- TOC entry 5192 (class 0 OID 124419)
-- Dependencies: 264
-- Data for Name: producto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.producto (producto_id, codigo, nombre, tipo, peso, unidad_id, descripcion, activo, created_at, updated_at, precio_unitario) FROM stdin;
1	CAFE-UNIVALLE-500G	Café Univalle Orgánico 500 g	marca_univalle	0.50	1	Café orgánico marca Univalle	t	\N	\N	45.00
2	MIEL-UNIVALLE-350G	Miel Univalle Pura 350 g	marca_univalle	0.35	1	Miel pura marca Univalle	t	\N	\N	28.50
3	GRANOLA-UNIVALLE-750G	Granola Univalle Natural 750 g	marca_univalle	0.75	1	Granola natural marca Univalle	t	\N	\N	32.00
4	YOGUR-BIO-NATURAL-1L	Yogur Univalle Bio Natural 1 L	marca_univalle	1.00	3	Yogur bio natural marca Univalle	t	\N	\N	18.50
5	YOGUR-BIO-FRUTILLA-1L	Yogur Univalle Bio Frutilla 1 L	marca_univalle	1.00	3	Yogur bio frutilla marca Univalle	t	\N	\N	19.00
6	HARINA-INTEGRAL-1KG	Harina Integral Univalle Vital 1 kg	marca_univalle	1.00	1	Harina integral marca Univalle	t	\N	\N	15.50
7	AVENA-ORGANICA-900G	Avena Univalle Orgánica 900 g	organico	0.90	1	Avena orgánica marca Univalle	t	\N	\N	22.00
8	CHOCOLATE-AMARGO-100G	Chocolate Amargo Univalle 70% 100 g	marca_univalle	0.10	1	Chocolate amargo 70% marca Univalle	t	\N	\N	12.50
9	QUINUA-REAL-1KG	Quinua Real Univalle 1 kg	marca_univalle	1.00	1	Quinua real marca Univalle	t	\N	\N	38.00
10	ARROZ-INTEGRAL-1KG	Arroz Integral Univalle 1 kg	marca_univalle	1.00	1	Arroz integral marca Univalle	t	\N	\N	16.00
11	ACEITE-COCO-300ML	Aceite de Coco Univalle 300 ml	marca_univalle	0.30	3	Aceite de coco marca Univalle	t	\N	\N	42.00
12	PAN-INTEGRAL-600G	Pan Integral Univalle 600 g	marca_univalle	0.60	1	Pan integral marca Univalle	t	\N	\N	14.50
13	FRUTOS-SECOS-MIX-250G	Frutos Secos Univalle Mix 250 g	marca_univalle	0.25	1	Mix de frutos secos marca Univalle	t	\N	\N	35.00
14	GALLETAS-INTEGRALES-200G	Galletas Integrales Univalle 200 g	marca_univalle	0.20	1	Galletas integrales marca Univalle	t	\N	\N	11.50
15	SIROPE-AGAVE-250ML	Sirope de Agave Univalle 250 ml	marca_univalle	0.25	3	Sirope de agave marca Univalle	t	\N	\N	24.00
16	TE-VERDE-20SOBRES	Té Verde Univalle Orgánico 20 sobres	organico	0.05	2	Té verde orgánico marca Univalle	t	\N	\N	18.00
17	MANTEQUILLA-MANI-350G	Mantequilla de Maní Univalle 350 g	marca_univalle	0.35	1	Mantequilla de maní marca Univalle	t	\N	\N	26.50
18	LENTEJAS-ORGANICAS-900G	Lentejas Univalle Orgánicas 900 g	organico	0.90	1	Lentejas orgánicas marca Univalle	t	\N	\N	20.00
19	CEREAL-MAIZ-500G	Cereal de Maíz Univalle 500 g	marca_univalle	0.50	1	Cereal de maíz marca Univalle	t	\N	\N	13.50
20	PASTA-INTEGRAL-500G	Pasta Integral Univalle 500 g	marca_univalle	0.50	1	Pasta integral marca Univalle	t	\N	\N	17.00
\.


--
-- TOC entry 5196 (class 0 OID 124485)
-- Dependencies: 268
-- Data for Name: producto_destino_pedido; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.producto_destino_pedido (producto_destino_id, destino_id, producto_pedido_id, cantidad, observaciones, created_at, updated_at) FROM stdin;
1	1	3	20.0000	\N	2025-12-10 04:57:13	2025-12-10 04:57:13
2	2	1	10.0000	\N	2025-12-10 04:57:13	2025-12-10 04:57:13
4	2	3	30.0000	\N	2025-12-10 04:57:13	2025-12-10 04:57:13
5	5	8	15.0000	\N	2025-12-10 06:00:17	2025-12-10 06:00:17
6	7	9	15.0000	\N	2025-12-10 06:20:58	2025-12-10 06:20:58
7	8	11	20.0000	\N	2025-12-10 06:20:58	2025-12-10 06:20:58
8	10	12	15.0000	\N	2025-12-10 19:47:23	2025-12-10 19:47:23
9	12	13	10.0000	\N	2025-12-11 01:11:11	2025-12-11 01:11:11
10	13	14	10.0000	\N	2025-12-11 01:47:15	2025-12-11 01:47:15
20	18	18	5.0000	\N	2025-12-11 02:22:55	2025-12-11 02:22:55
21	19	18	5.0000	\N	2025-12-11 02:22:55	2025-12-11 02:22:55
22	20	19	60.0000	Cantidad para este destino - almacén principal	2025-12-11 02:52:27	2025-12-11 02:52:27
23	20	20	30.0000	Producto para distribución local	2025-12-11 02:52:27	2025-12-11 02:52:27
24	20	21	40.0000	Manejar con cuidado	2025-12-11 02:52:27	2025-12-11 02:52:27
25	21	19	40.5000	Resto del producto 1 para este destino	2025-12-11 02:52:27	2025-12-11 02:52:27
26	21	20	20.2500	Resto del producto 2	2025-12-11 02:52:27	2025-12-11 02:52:27
27	21	21	35.7500	Resto del producto 3	2025-12-11 02:52:27	2025-12-11 02:52:27
28	22	19	0.0001	Cantidad mínima para este destino	2025-12-11 02:52:27	2025-12-11 02:52:27
29	22	20	0.0001	Cantidad mínima para este destino	2025-12-11 02:52:27	2025-12-11 02:52:27
30	22	21	0.0001	Cantidad mínima para este destino	2025-12-11 02:52:27	2025-12-11 02:52:27
31	23	22	10.0000	\N	2025-12-11 15:00:02	2025-12-11 15:00:02
32	24	23	60.0000	Cantidad para este destino - almacén principal	2025-12-12 19:54:47	2025-12-12 19:54:47
33	24	24	30.0000	Producto para distribución local	2025-12-12 19:54:47	2025-12-12 19:54:47
34	24	25	40.0000	Manejar con cuidado	2025-12-12 19:54:47	2025-12-12 19:54:47
35	25	23	40.5000	Resto del producto 1 para este destino	2025-12-12 19:54:47	2025-12-12 19:54:47
36	25	24	20.2500	Resto del producto 2	2025-12-12 19:54:47	2025-12-12 19:54:47
37	25	25	35.7500	Resto del producto 3	2025-12-12 19:54:47	2025-12-12 19:54:47
38	26	23	0.0001	Cantidad mínima para este destino	2025-12-12 19:54:47	2025-12-12 19:54:47
39	26	24	0.0001	Cantidad mínima para este destino	2025-12-12 19:54:47	2025-12-12 19:54:47
40	26	25	0.0001	Cantidad mínima para este destino	2025-12-12 19:54:47	2025-12-12 19:54:47
41	28	26	10.0000	\N	2025-12-12 21:43:05	2025-12-12 21:43:05
42	30	27	10.0000	\N	2025-12-13 03:49:56	2025-12-13 03:49:56
43	32	28	10.0000	\N	2025-12-13 04:08:28	2025-12-13 04:08:28
44	34	29	10.0000	\N	2025-12-14 12:03:52	2025-12-14 12:03:52
45	36	30	10.0000	\N	2025-12-14 12:10:13	2025-12-14 12:10:13
46	38	31	10.0000	\N	2025-12-14 13:52:40	2025-12-14 13:52:40
\.


--
-- TOC entry 5194 (class 0 OID 124453)
-- Dependencies: 266
-- Data for Name: producto_pedido; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.producto_pedido (producto_pedido_id, pedido_id, producto_id, cantidad, estado, razon_rechazo, aprobado_por, aprobado_en, observaciones, created_at, updated_at, precio) FROM stdin;
1	1	2	10.0000	aprobado	\N	1	2025-12-10 04:57:27	\N	2025-12-10 04:57:13	2025-12-10 04:57:27	285.00
3	1	8	50.0000	aprobado	\N	1	2025-12-10 04:57:27	\N	2025-12-10 04:57:13	2025-12-10 04:57:27	625.00
7	1	15	10.0000	aprobado	\N	1	2025-12-10 04:57:27	\N	2025-12-10 04:57:13	2025-12-10 04:57:27	240.00
8	2	2	15.0000	aprobado	\N	1	2025-12-10 06:00:30	\N	2025-12-10 06:00:17	2025-12-10 06:00:30	427.50
11	3	11	20.0000	aprobado	\N	1	2025-12-10 06:21:20	\N	2025-12-10 06:20:58	2025-12-10 06:21:20	840.00
9	3	17	15.0000	aprobado	\N	1	2025-12-10 06:21:20	\N	2025-12-10 06:20:58	2025-12-10 06:21:20	397.50
12	4	17	15.0000	aprobado	\N	1	2025-12-10 19:47:39	\N	2025-12-10 19:47:23	2025-12-10 19:47:39	397.50
14	6	1	10.0000	pendiente	\N	\N	\N	\N	2025-12-11 01:47:15	2025-12-11 01:47:15	\N
18	8	6	10.0000	aprobado	\N	1	2025-12-11 02:24:15	\N	2025-12-11 02:22:54	2025-12-11 02:24:15	155.00
19	7	1	100.5000	aprobado	\N	1	2025-12-11 14:52:02	\N	2025-12-11 02:52:27	2025-12-11 14:52:02	4522.50
20	7	2	50.2500	aprobado	\N	1	2025-12-11 14:52:02	\N	2025-12-11 02:52:27	2025-12-11 14:52:02	1432.13
21	7	3	75.7500	aprobado	\N	1	2025-12-11 14:52:02	\N	2025-12-11 02:52:27	2025-12-11 14:52:02	2424.00
22	9	9	10.0000	aprobado	\N	1	2025-12-11 15:00:14	\N	2025-12-11 15:00:02	2025-12-11 15:00:14	380.00
23	10	1	100.5000	pendiente	\N	\N	\N	Producto con especificaciones especiales. Requiere manejo cuidadoso.	2025-12-12 19:54:47	2025-12-12 19:54:47	4522.50
24	10	2	50.2500	pendiente	\N	\N	\N	\N	2025-12-12 19:54:47	2025-12-12 19:54:47	1432.13
25	10	3	75.7500	pendiente	\N	\N	\N	Producto frágil, embalaje especial	2025-12-12 19:54:47	2025-12-12 19:54:47	2424.00
13	5	17	10.0000	aprobado	\N	1	2025-12-12 21:21:15	\N	2025-12-11 01:11:11	2025-12-12 21:21:15	265.00
26	11	20	10.0000	aprobado	\N	1	2025-12-12 21:44:14	\N	2025-12-12 21:43:05	2025-12-12 21:44:14	170.00
27	12	6	10.0000	aprobado	\N	1	2025-12-13 03:50:13	\N	2025-12-13 03:49:56	2025-12-13 03:50:13	155.00
28	13	3	10.0000	aprobado	\N	1	2025-12-13 04:08:47	\N	2025-12-13 04:08:28	2025-12-13 04:08:47	320.00
29	14	17	10.0000	rechazado	no se puede	1	2025-12-14 12:09:13	\N	2025-12-14 12:03:52	2025-12-14 12:09:13	265.00
30	15	15	10.0000	aprobado	\N	1	2025-12-14 12:10:29	\N	2025-12-14 12:10:13	2025-12-14 12:10:29	240.00
31	16	2	10.0000	pendiente	\N	\N	\N	\N	2025-12-14 13:52:40	2025-12-14 13:52:40	285.00
\.


--
-- TOC entry 5185 (class 0 OID 124336)
-- Dependencies: 257
-- Data for Name: proveedor; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.proveedor (proveedor_id, razon_social, nombre_comercial, nit, contacto, telefono, email, direccion, activo) FROM stdin;
1	sancor	sancor srl	123456789	jp	+59178113449	jhairaguilar483.est@gmail.com	ALAMACEN 2	t
\.


--
-- TOC entry 5199 (class 0 OID 124534)
-- Dependencies: 271
-- Data for Name: registro_movimiento_material; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.registro_movimiento_material (registro_id, material_id, tipo_movimiento_id, operador_id, cantidad, saldo_anterior, saldo_nuevo, descripcion, observaciones, fecha_movimiento) FROM stdin;
1	1	1	1	100.0000	0.0000	100.0000	Recepción de materia prima (Conforme)	\N	2025-12-10 01:18:08
2	1	2	1	100.0000	100.0000	0.0000	Descuento por creación de lote (Código: LOTE-0001-20251210)	\N	2025-12-10 01:23:18
3	2	1	1	200.0000	0.0000	200.0000	Recepción de materia prima (Conforme)	\N	2025-12-10 02:01:57
4	2	2	1	150.0000	200.0000	50.0000	Descuento por creación de lote (Código: LOTE-0002-20251210)	\N	2025-12-10 02:02:29
5	1	1	1	100.0000	0.0000	100.0000	Recepción de materia prima (Conforme)	\N	2025-12-10 02:22:14
6	1	2	1	100.0000	100.0000	0.0000	Descuento por creación de lote (Código: LOTE-0003-20251210)	\N	2025-12-10 02:24:04
7	2	2	1	50.0000	50.0000	0.0000	Descuento por creación de lote (Código: LOTE-0003-20251210)	\N	2025-12-10 02:24:04
8	1	1	1	500.0000	0.0000	500.0000	Recepción de materia prima (Conforme)	\N	2025-12-10 15:49:03
9	1	2	1	100.0000	500.0000	400.0000	Descuento por creación de lote (Código: LOTE-0004-20251210)	\N	2025-12-10 15:49:36
10	2	1	1	200.0000	0.0000	200.0000	Recepción de materia prima (Conforme)	\N	2025-12-11 10:22:43
11	2	2	1	100.0000	200.0000	100.0000	Descuento por creación de lote (Código: LOTE-0005-20251211)	\N	2025-12-11 10:23:34
12	2	1	1	100.0000	100.0000	200.0000	Recepción de materia prima (Conforme)	\N	2025-12-11 10:52:55
13	2	2	1	100.0000	200.0000	100.0000	Descuento por creación de lote (Código: LOTE-0006-20251211)	\N	2025-12-11 10:53:24
14	1	1	1	100.0000	400.0000	500.0000	Recepción de materia prima (Conforme)	\N	2025-12-11 11:00:53
15	1	2	1	100.0000	500.0000	400.0000	Descuento por creación de lote (Código: LOTE-0007-20251211)	\N	2025-12-11 11:01:22
16	1	1	1	100.0000	400.0000	500.0000	Recepción de materia prima (Conforme)	\N	2025-12-12 17:21:51
17	1	2	1	100.0000	500.0000	400.0000	Descuento por creación de lote (Código: LOTE-0008-20251212)	\N	2025-12-12 17:22:10
18	1	2	1	100.0000	400.0000	300.0000	Descuento por creación de lote (Código: LOTE-0009-20251212)	\N	2025-12-12 17:45:14
19	1	1	1	100.0000	300.0000	400.0000	Recepción de materia prima (Conforme)	\N	2025-12-12 23:50:59
20	1	2	1	100.0000	400.0000	300.0000	Descuento por creación de lote (Código: LOTE-0010-20251213)	\N	2025-12-12 23:51:20
21	1	1	1	100.0000	300.0000	400.0000	Recepción de materia prima (Conforme)	\N	2025-12-13 00:10:48
22	1	2	1	100.0000	400.0000	300.0000	Descuento por creación de lote (Código: LOTE-0011-20251213)	\N	2025-12-13 00:11:12
23	3	1	1	50.0000	0.0000	50.0000	Recepción de materia prima (Conforme)	\N	2025-12-14 09:06:25
24	1	2	1	10.0000	300.0000	290.0000	Descuento por creación de lote (Código: LOTE-0012-20251214)	\N	2025-12-14 09:07:40
25	1	1	1	100.0000	290.0000	390.0000	Recepción de materia prima (Conforme)	\N	2025-12-14 09:56:27
\.


--
-- TOC entry 5202 (class 0 OID 124583)
-- Dependencies: 274
-- Data for Name: registro_proceso_maquina; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.registro_proceso_maquina (registro_id, lote_id, proceso_maquina_id, operador_id, variables_ingresadas, cumple_estandar, observaciones, hora_inicio, hora_fin, fecha_registro) FROM stdin;
1	1	1	1	{"VELOCIDAD":"10"}	t	no	2025-12-10 05:47:58	2025-12-10 05:47:58	2025-12-10 05:47:58
2	2	1	1	{"VELOCIDAD":"10"}	t	no	2025-12-10 06:02:51	2025-12-10 06:02:51	2025-12-10 06:02:51
3	3	2	1	{"PRESION":"100"}	t	ok	2025-12-10 06:25:17	2025-12-10 06:25:17	2025-12-10 06:25:17
4	3	3	1	{"TIEMPO":"30"}	t	ok	2025-12-10 06:25:24	2025-12-10 06:25:24	2025-12-10 06:25:24
5	4	1	1	{"VELOCIDAD":"10"}	t	\N	2025-12-10 19:49:53	2025-12-10 19:49:53	2025-12-10 19:49:53
6	5	1	1	{"VELOCIDAD":"10"}	t	\N	2025-12-11 14:23:58	2025-12-11 14:23:58	2025-12-11 14:23:58
7	6	1	1	{"VELOCIDAD":"10"}	t	no	2025-12-11 14:54:32	2025-12-11 14:54:32	2025-12-11 14:54:32
8	7	1	1	{"VELOCIDAD":"10"}	t	\N	2025-12-11 15:01:56	2025-12-11 15:01:56	2025-12-11 15:01:56
9	8	1	1	{"VELOCIDAD":"10"}	t	no	2025-12-12 21:22:31	2025-12-12 21:22:31	2025-12-12 21:22:31
10	9	1	1	{"VELOCIDAD":"10"}	t	\N	2025-12-12 21:47:04	2025-12-12 21:47:04	2025-12-12 21:47:04
11	10	1	1	{"VELOCIDAD":"10"}	t	no	2025-12-13 03:51:47	2025-12-13 03:51:47	2025-12-13 03:51:47
12	11	1	1	{"VELOCIDAD":"10"}	t	\N	2025-12-13 04:11:39	2025-12-13 04:11:39	2025-12-13 04:11:39
13	12	1	1	{"VELOCIDAD":"10"}	t	\N	2025-12-14 13:08:09	2025-12-14 13:08:09	2025-12-14 13:08:09
\.


--
-- TOC entry 5207 (class 0 OID 124663)
-- Dependencies: 279
-- Data for Name: respuesta_proveedor; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.respuesta_proveedor (respuesta_id, solicitud_id, proveedor_id, fecha_respuesta, cantidad_confirmada, fecha_entrega, observaciones, precio) FROM stdin;
\.


--
-- TOC entry 5180 (class 0 OID 124285)
-- Dependencies: 252
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
2	1
3	1
4	1
5	1
6	1
19	1
1	2
10	2
11	2
12	2
13	2
14	2
15	2
16	2
17	2
18	2
19	2
20	2
7	2
8	2
9	2
1	3
2	3
3	3
4	3
5	3
6	3
7	3
8	3
9	3
10	3
11	3
12	3
13	3
14	3
15	3
16	3
17	3
18	3
19	3
20	3
21	3
\.


--
-- TOC entry 5177 (class 0 OID 124253)
-- Dependencies: 249
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
1	cliente	web	2025-12-09 18:27:56	2025-12-09 18:27:56
2	operador	web	2025-12-09 18:27:56	2025-12-09 18:27:56
3	admin	web	2025-12-09 18:27:56	2025-12-09 18:27:56
\.


--
-- TOC entry 5209 (class 0 OID 124682)
-- Dependencies: 281
-- Data for Name: seguimiento_envio_pedido; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.seguimiento_envio_pedido (id, pedido_id, destino_id, envio_id, codigo_envio, estado, mensaje_error, datos_solicitud, datos_respuesta, created_at, updated_at) FROM stdin;
1	1	1	\N	\N	failed	No hay almacenes disponibles en plantaCruds para la ubicación de recojo: Calle H. Salazar, 359, Centro, Santa Cruz de la Sierra, Santa Cruz, Bolivia	\N	\N	2025-12-10 05:56:13	2025-12-10 05:56:13
2	1	2	\N	\N	failed	No hay almacenes disponibles en plantaCruds para la ubicación de recojo: Calle H. Salazar, 359, Centro, Santa Cruz de la Sierra, Santa Cruz, Bolivia	\N	\N	2025-12-10 05:56:13	2025-12-10 05:56:13
3	1	3	\N	\N	failed	No hay almacenes disponibles en plantaCruds para la ubicación de recojo: Calle H. Salazar, 359, Centro, Santa Cruz de la Sierra, Santa Cruz, Bolivia	\N	\N	2025-12-10 05:56:13	2025-12-10 05:56:13
4	2	5	3	ENV-251210-714692	success	\N	\N	{"success":true,"message":"Env\\u00edo creado exitosamente","data":{"codigo":"ENV-251210-714692","almacen_destino_id":2,"categoria":"general","fecha_creacion":"2025-12-10T00:00:00.000000Z","fecha_estimada_entrega":"2025-12-14T00:00:00.000000Z","hora_estimada":"14:00","estado":"pendiente","observaciones":"Pedido: PED-0002-20251210\\nCliente: jhair aguilar\\n\\n\\ud83d\\udccd UBICACI\\u00d3N DE RECOJO:\\nDirecci\\u00f3n: Avenida Perimetral, Estaci\\u00f3n Argentina, Santa Cruz de la Sierra, Santa Cruz, Bolivia\\nReferencia: casa rosada\\nCoordenadas: -17.79907558, -63.16821098\\n\\nInstrucciones de entrega: no\\nContacto: jhair cliente aguilar - Tel: 78113449\\nDirecci\\u00f3n de entrega: alm1 (ninguna)","total_cantidad":15,"total_peso":"5.250","total_precio":"0.00","updated_at":"2025-12-10T06:03:13.000000Z","created_at":"2025-12-10T06:03:13.000000Z","id":3,"almacen_destino":{"id":2,"nombre":"Almac\\u00e9n Centro","usuario_almacen_id":1,"latitud":"-17.7890000","longitud":"-63.1800000","direccion_completa":"Calle Libertad, Santa Cruz","es_planta":false,"activo":true,"created_at":"2025-12-08T14:17:35.000000Z","updated_at":"2025-12-08T14:17:35.000000Z"},"productos":[{"id":3,"envio_id":3,"producto_nombre":"Miel Univalle Pura 350 g","cantidad":15,"peso_unitario":"0.350","unidad_medida_id":null,"tipo_empaque_id":null,"precio_unitario":"0.00","total_peso":"5.250","total_precio":"0.00","created_at":"2025-12-10T06:03:13.000000Z","updated_at":"2025-12-10T06:03:13.000000Z"}]},"qr_code":null}	2025-12-10 06:03:20	2025-12-10 06:03:20
5	3	7	4	ENV-251210-3891FA	success	\N	\N	{"success":true,"message":"Env\\u00edo creado exitosamente","data":{"codigo":"ENV-251210-3891FA","almacen_destino_id":2,"categoria":"general","fecha_creacion":"2025-12-10T00:00:00.000000Z","fecha_estimada_entrega":"2025-12-14T00:00:00.000000Z","hora_estimada":"14:00","estado":"pendiente","observaciones":"Pedido: PED-0003-20251210\\nCliente: jhair aguilar\\n\\n\\ud83d\\udccd UBICACI\\u00d3N DE RECOJO:\\nDirecci\\u00f3n: Calle H\\u00e9roes del Chaco, El Pari, Santa Cruz de la Sierra, Santa Cruz, Bolivia\\nReferencia: casa rosada\\nCoordenadas: -17.79897854, -63.18709373\\n\\nInstrucciones de entrega: no\\nContacto: jhair cliente aguilar - Tel: 78113449\\nDirecci\\u00f3n de entrega: almacen 1 (ninguna)","total_cantidad":15,"total_peso":"5.250","total_precio":"0.00","updated_at":"2025-12-10T06:27:22.000000Z","created_at":"2025-12-10T06:27:22.000000Z","id":4,"almacen_destino":{"id":2,"nombre":"Almac\\u00e9n Centro","usuario_almacen_id":1,"latitud":"-17.7890000","longitud":"-63.1800000","direccion_completa":"Calle Libertad, Santa Cruz","es_planta":false,"activo":true,"created_at":"2025-12-08T14:17:35.000000Z","updated_at":"2025-12-08T14:17:35.000000Z"},"productos":[{"id":4,"envio_id":4,"producto_nombre":"Mantequilla de Man\\u00ed Univalle 350 g","cantidad":15,"peso_unitario":"0.350","unidad_medida_id":null,"tipo_empaque_id":null,"precio_unitario":"0.00","total_peso":"5.250","total_precio":"0.00","created_at":"2025-12-10T06:27:22.000000Z","updated_at":"2025-12-10T06:27:22.000000Z"}]},"qr_code":null}	2025-12-10 06:27:33	2025-12-10 06:27:33
6	3	8	5	ENV-251210-7A5185	success	\N	\N	{"success":true,"message":"Env\\u00edo creado exitosamente","data":{"codigo":"ENV-251210-7A5185","almacen_destino_id":2,"categoria":"general","fecha_creacion":"2025-12-10T00:00:00.000000Z","fecha_estimada_entrega":"2025-12-14T00:00:00.000000Z","hora_estimada":"14:00","estado":"pendiente","observaciones":"Pedido: PED-0003-20251210\\nCliente: jhair aguilar\\n\\n\\ud83d\\udccd UBICACI\\u00d3N DE RECOJO:\\nDirecci\\u00f3n: Calle H\\u00e9roes del Chaco, El Pari, Santa Cruz de la Sierra, Santa Cruz, Bolivia\\nReferencia: casa rosada\\nCoordenadas: -17.79897854, -63.18709373\\n\\nInstrucciones de entrega: no\\nContacto: jhair cliente aguilar - Tel: 78113449\\nDirecci\\u00f3n de entrega: almacen 2 (ninguna)","total_cantidad":20,"total_peso":"6.000","total_precio":"0.00","updated_at":"2025-12-10T06:27:28.000000Z","created_at":"2025-12-10T06:27:28.000000Z","id":5,"almacen_destino":{"id":2,"nombre":"Almac\\u00e9n Centro","usuario_almacen_id":1,"latitud":"-17.7890000","longitud":"-63.1800000","direccion_completa":"Calle Libertad, Santa Cruz","es_planta":false,"activo":true,"created_at":"2025-12-08T14:17:35.000000Z","updated_at":"2025-12-08T14:17:35.000000Z"},"productos":[{"id":5,"envio_id":5,"producto_nombre":"Aceite de Coco Univalle 300 ml","cantidad":20,"peso_unitario":"0.300","unidad_medida_id":null,"tipo_empaque_id":null,"precio_unitario":"0.00","total_peso":"6.000","total_precio":"0.00","created_at":"2025-12-10T06:27:28.000000Z","updated_at":"2025-12-10T06:27:28.000000Z"}]},"qr_code":null}	2025-12-10 06:27:33	2025-12-10 06:27:33
7	4	10	\N	\N	failed	No hay almacenes disponibles en plantaCruds para la ubicación de recojo: Calle Toledo Pimentel, Centro, Santa Cruz de la Sierra, Santa Cruz, Bolivia	\N	\N	2025-12-10 20:27:54	2025-12-10 20:27:54
8	8	18	\N	\N	failed	No hay almacenes disponibles en plantaCruds para la ubicación de recojo: Santa Cruz de la Sierra, Santa Cruz, Bolivia	\N	\N	2025-12-11 14:43:01	2025-12-11 14:43:01
9	8	19	\N	\N	failed	No hay almacenes disponibles en plantaCruds para la ubicación de recojo: Santa Cruz de la Sierra, Santa Cruz, Bolivia	\N	\N	2025-12-11 14:43:01	2025-12-11 14:43:01
10	9	23	6	ENV-251211-1BE45D	success	\N	\N	{"success":true,"message":"Env\\u00edo creado exitosamente","data":{"codigo":"ENV-251211-1BE45D","almacen_destino_id":2,"categoria":"general","fecha_creacion":"2025-12-11T00:00:00.000000Z","fecha_estimada_entrega":"2025-12-17T00:00:00.000000Z","hora_estimada":"14:00","estado":"pendiente","observaciones":"Pedido: PED-0009-20251211\\nCliente: jhair aguilar\\n\\n\\ud83d\\udccd UBICACI\\u00d3N DE RECOJO:\\nDirecci\\u00f3n: Calle Campo Rosa del Sara, Estaci\\u00f3n Argentina, Santa Cruz de la Sierra, Santa Cruz, Bolivia\\nReferencia: casa rosada\\nCoordenadas: -17.80064061, -63.16347647\\n\\nInstrucciones de entrega: no\\nContacto: jhair cliente aguilar - Tel: 78113449\\nDirecci\\u00f3n de entrega: no (NO)","total_cantidad":10,"total_peso":"10.000","total_precio":"0.00","updated_at":"2025-12-11T15:03:14.000000Z","created_at":"2025-12-11T15:03:14.000000Z","id":6,"almacen_destino":{"id":2,"nombre":"Almac\\u00e9n Centro","usuario_almacen_id":1,"latitud":"-17.7890000","longitud":"-63.1800000","direccion_completa":"Calle Libertad, Santa Cruz","es_planta":false,"activo":true,"created_at":"2025-12-08T14:17:35.000000Z","updated_at":"2025-12-08T14:17:35.000000Z"},"productos":[{"id":6,"envio_id":6,"producto_nombre":"Quinua Real Univalle 1 kg","cantidad":10,"peso_unitario":"1.000","unidad_medida_id":null,"tipo_empaque_id":null,"precio_unitario":"0.00","total_peso":"10.000","total_precio":"0.00","created_at":"2025-12-11T15:03:14.000000Z","updated_at":"2025-12-11T15:03:14.000000Z"}]},"qr_code":null}	2025-12-11 15:03:21	2025-12-11 15:03:21
11	7	20	\N	\N	failed	Error al crear envío en plantaCruds (HTTP 500): {"success":false,"message":"Error al crear env\\u00edo: Error al procesar producto en posici\\u00f3n 0: SQLSTATE[42703]: Undefined column: 7 ERROR:  no existe la columna \\u00abproducto_id\\u00bb en la relaci\\u00f3n \\u00abenvio_productos\\u00bb\\nLINE 1: insert into \\"envio_productos\\" (\\"envio_id\\", \\"producto_id\\", \\"p...\\n                                                   ^ (Connection: pgsql, SQL: insert into \\"envio_productos\\" (\\"envio_id\\", \\"producto_id\\", \\"producto_nombre\\", \\"cantidad\\", \\"peso_unitario\\", \\"precio_unitario\\", \\"total_peso\\", \\"total_precio\\", \\"updated_at\\", \\"created_at\\") values (7, 8, Caf\\u00e9 Univalle Org\\u00e1nico 500 g, 60, 0.5, 0, 30, 0, 2025-12-12 20:34:29, 2025-12-12 20:34:29) returning \\"id\\")","error_details":"#0 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Controller.php(54): App\\\\Http\\\\Controllers\\\\Api\\\\EnvioApiController->store(Object(Illuminate\\\\Http\\\\Request))\\n#1 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\ControllerDispatcher.php(43): Illuminate\\\\Routing\\\\Controller->callAction('store', Array)\\n#2 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Route.php(265): Illuminate\\\\Routing\\\\ControllerDispatcher->dispatch(Object(Illuminate\\\\Routing\\\\Route), Object(App\\\\Http\\\\Controllers\\\\Api\\\\EnvioApiController), 'store')\\n#3 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Route.php(211): Illuminate\\\\Routing\\\\Route->runController()\\n#4 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Router.php(822): Illuminate\\\\Routing\\\\Route->run()\\n#5 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(180): Illuminate\\\\Routing\\\\Router->Illuminate\\\\Routing\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#6 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Http\\\\Middleware\\\\HandleCors.php(61): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#7 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Http\\\\Middleware\\\\HandleCors->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#8 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Middleware\\\\SubstituteBindings.php(50): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#9 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Routing\\\\Middleware\\\\SubstituteBindings->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#10 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(137): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#11 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Router.php(821): Illuminate\\\\Pipeline\\\\Pipeline->then(Object(Closure))\\n#12 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Router.php(800): Illuminate\\\\Routing\\\\Router->runRouteWithinStack(Object(Illuminate\\\\Routing\\\\Route), Object(Illuminate\\\\Http\\\\Request))\\n#13 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Router.php(764): Illuminate\\\\Routing\\\\Router->runRoute(Object(Illuminate\\\\Http\\\\Request), Object(Illuminate\\\\Routing\\\\Route))\\n#14 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Router.php(753): Illuminate\\\\Routing\\\\Router->dispatchToRoute(Object(Illuminate\\\\Http\\\\Request))\\n#15 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Kernel.php(200): Illuminate\\\\Routing\\\\Router->dispatch(Object(Illuminate\\\\Http\\\\Request))\\n#16 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(180): Illuminate\\\\Foundation\\\\Http\\\\Kernel->Illuminate\\\\Foundation\\\\Http\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#17 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TransformsRequest.php(21): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#18 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\ConvertEmptyStringsToNull.php(31): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TransformsRequest->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#19 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\ConvertEmptyStringsToNull->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#20 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TransformsRequest.php(21): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#21 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TrimStrings.php(51): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TransformsRequest->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#22 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TrimStrings->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#23 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Http\\\\Middleware\\\\ValidatePostSize.php(27): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#24 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Http\\\\Middleware\\\\ValidatePostSize->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#25 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\PreventRequestsDuringMaintenance.php(109): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#26 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\PreventRequestsDuringMaintenance->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#27 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Http\\\\Middleware\\\\HandleCors.php(61): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#28 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Http\\\\Middleware\\\\HandleCors->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#29 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Http\\\\Middleware\\\\TrustProxies.php(58): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#30 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Http\\\\Middleware\\\\TrustProxies->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#31 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\InvokeDeferredCallbacks.php(22): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#32 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\InvokeDeferredCallbacks->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#33 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Http\\\\Middleware\\\\ValidatePathEncoding.php(26): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#34 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Http\\\\Middleware\\\\ValidatePathEncoding->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#35 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(137): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#36 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Kernel.php(175): Illuminate\\\\Pipeline\\\\Pipeline->then(Object(Closure))\\n#37 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Kernel.php(144): Illuminate\\\\Foundation\\\\Http\\\\Kernel->sendRequestThroughRouter(Object(Illuminate\\\\Http\\\\Request))\\n#38 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Application.php(1220): Illuminate\\\\Foundation\\\\Http\\\\Kernel->handle(Object(Illuminate\\\\Http\\\\Request))\\n#39 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\public\\\\index.php(20): Illuminate\\\\Foundation\\\\Application->handleRequest(Object(Illuminate\\\\Http\\\\Request))\\n#40 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\resources\\\\server.php(23): require_once('C:\\\\\\\\Users\\\\\\\\jhair\\\\\\\\...')\\n#41 {main}"}	\N	\N	2025-12-12 20:34:31	2025-12-12 20:34:31
12	7	21	\N	\N	failed	Error al crear envío en plantaCruds (HTTP 500): {"success":false,"message":"Error al crear env\\u00edo: Error al procesar producto en posici\\u00f3n 0: SQLSTATE[42703]: Undefined column: 7 ERROR:  no existe la columna \\u00abproducto_id\\u00bb en la relaci\\u00f3n \\u00abenvio_productos\\u00bb\\nLINE 1: insert into \\"envio_productos\\" (\\"envio_id\\", \\"producto_id\\", \\"p...\\n                                                   ^ (Connection: pgsql, SQL: insert into \\"envio_productos\\" (\\"envio_id\\", \\"producto_id\\", \\"producto_nombre\\", \\"cantidad\\", \\"peso_unitario\\", \\"precio_unitario\\", \\"total_peso\\", \\"total_precio\\", \\"updated_at\\", \\"created_at\\") values (8, 9, Caf\\u00e9 Univalle Org\\u00e1nico 500 g, 40.5, 0.5, 0, 20, 0, 2025-12-12 20:34:30, 2025-12-12 20:34:30) returning \\"id\\")","error_details":"#0 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Controller.php(54): App\\\\Http\\\\Controllers\\\\Api\\\\EnvioApiController->store(Object(Illuminate\\\\Http\\\\Request))\\n#1 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\ControllerDispatcher.php(43): Illuminate\\\\Routing\\\\Controller->callAction('store', Array)\\n#2 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Route.php(265): Illuminate\\\\Routing\\\\ControllerDispatcher->dispatch(Object(Illuminate\\\\Routing\\\\Route), Object(App\\\\Http\\\\Controllers\\\\Api\\\\EnvioApiController), 'store')\\n#3 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Route.php(211): Illuminate\\\\Routing\\\\Route->runController()\\n#4 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Router.php(822): Illuminate\\\\Routing\\\\Route->run()\\n#5 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(180): Illuminate\\\\Routing\\\\Router->Illuminate\\\\Routing\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#6 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Http\\\\Middleware\\\\HandleCors.php(61): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#7 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Http\\\\Middleware\\\\HandleCors->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#8 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Middleware\\\\SubstituteBindings.php(50): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#9 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Routing\\\\Middleware\\\\SubstituteBindings->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#10 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(137): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#11 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Router.php(821): Illuminate\\\\Pipeline\\\\Pipeline->then(Object(Closure))\\n#12 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Router.php(800): Illuminate\\\\Routing\\\\Router->runRouteWithinStack(Object(Illuminate\\\\Routing\\\\Route), Object(Illuminate\\\\Http\\\\Request))\\n#13 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Router.php(764): Illuminate\\\\Routing\\\\Router->runRoute(Object(Illuminate\\\\Http\\\\Request), Object(Illuminate\\\\Routing\\\\Route))\\n#14 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Routing\\\\Router.php(753): Illuminate\\\\Routing\\\\Router->dispatchToRoute(Object(Illuminate\\\\Http\\\\Request))\\n#15 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Kernel.php(200): Illuminate\\\\Routing\\\\Router->dispatch(Object(Illuminate\\\\Http\\\\Request))\\n#16 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(180): Illuminate\\\\Foundation\\\\Http\\\\Kernel->Illuminate\\\\Foundation\\\\Http\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#17 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TransformsRequest.php(21): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#18 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\ConvertEmptyStringsToNull.php(31): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TransformsRequest->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#19 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\ConvertEmptyStringsToNull->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#20 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TransformsRequest.php(21): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#21 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TrimStrings.php(51): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TransformsRequest->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#22 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\TrimStrings->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#23 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Http\\\\Middleware\\\\ValidatePostSize.php(27): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#24 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Http\\\\Middleware\\\\ValidatePostSize->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#25 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\PreventRequestsDuringMaintenance.php(109): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#26 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\PreventRequestsDuringMaintenance->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#27 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Http\\\\Middleware\\\\HandleCors.php(61): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#28 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Http\\\\Middleware\\\\HandleCors->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#29 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Http\\\\Middleware\\\\TrustProxies.php(58): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#30 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Http\\\\Middleware\\\\TrustProxies->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#31 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\InvokeDeferredCallbacks.php(22): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#32 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Foundation\\\\Http\\\\Middleware\\\\InvokeDeferredCallbacks->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#33 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Http\\\\Middleware\\\\ValidatePathEncoding.php(26): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#34 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(219): Illuminate\\\\Http\\\\Middleware\\\\ValidatePathEncoding->handle(Object(Illuminate\\\\Http\\\\Request), Object(Closure))\\n#35 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Pipeline\\\\Pipeline.php(137): Illuminate\\\\Pipeline\\\\Pipeline->Illuminate\\\\Pipeline\\\\{closure}(Object(Illuminate\\\\Http\\\\Request))\\n#36 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Kernel.php(175): Illuminate\\\\Pipeline\\\\Pipeline->then(Object(Closure))\\n#37 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Http\\\\Kernel.php(144): Illuminate\\\\Foundation\\\\Http\\\\Kernel->sendRequestThroughRouter(Object(Illuminate\\\\Http\\\\Request))\\n#38 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Application.php(1220): Illuminate\\\\Foundation\\\\Http\\\\Kernel->handle(Object(Illuminate\\\\Http\\\\Request))\\n#39 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\public\\\\index.php(20): Illuminate\\\\Foundation\\\\Application->handleRequest(Object(Illuminate\\\\Http\\\\Request))\\n#40 C:\\\\Users\\\\jhair\\\\Downloads\\\\Calidad\\\\sistemaplanta\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\resources\\\\server.php(23): require_once('C:\\\\\\\\Users\\\\\\\\jhair\\\\\\\\...')\\n#41 {main}"}	\N	\N	2025-12-12 20:34:31	2025-12-12 20:34:31
13	7	22	\N	\N	failed	Error al crear envío en plantaCruds (HTTP 422): {"success":false,"message":"Error de validaci\\u00f3n: La cantidad debe ser mayor a 0, La cantidad debe ser mayor a 0, La cantidad debe ser mayor a 0","errors":{"productos.0.cantidad":["La cantidad debe ser mayor a 0"],"productos.1.cantidad":["La cantidad debe ser mayor a 0"],"productos.2.cantidad":["La cantidad debe ser mayor a 0"]}}	\N	\N	2025-12-12 20:34:31	2025-12-12 20:34:31
14	5	12	9	ENV-251212-EBF636	success	\N	\N	{"success":true,"message":"Env\\u00edo creado exitosamente","data":{"codigo":"ENV-251212-EBF636","almacen_destino_id":2,"categoria":"general","fecha_creacion":"2025-12-12T00:00:00.000000Z","fecha_estimada_entrega":"2025-12-17T00:00:00.000000Z","hora_estimada":"14:00","estado":"pendiente","observaciones":"Pedido: PED-0005-20251211\\nCliente: jhair cliente aguilar\\n\\n\\ud83d\\udccd UBICACI\\u00d3N DE RECOJO:\\nDirecci\\u00f3n: Nuevo Palmar, Santa Cruz de la Sierra, Santa Cruz, Bolivia\\nReferencia: casa rosada\\nCoordenadas: -17.83800324, -63.16471858\\n\\nInstrucciones de entrega: no\\nContacto: jhair cliente aguilar - Tel: 78113449\\nDirecci\\u00f3n de entrega: almacen 1 (NO)","total_cantidad":10,"total_peso":"3.500","total_precio":"0.00","updated_at":"2025-12-12T21:23:09.000000Z","created_at":"2025-12-12T21:23:09.000000Z","id":9,"almacen_destino":{"id":2,"nombre":"Almac\\u00e9n Centro","usuario_almacen_id":1,"latitud":"-17.7890000","longitud":"-63.1800000","direccion_completa":"Calle Libertad, Santa Cruz","es_planta":false,"activo":true,"created_at":"2025-12-08T14:17:35.000000Z","updated_at":"2025-12-08T14:17:35.000000Z"},"productos":[{"id":7,"envio_id":9,"producto_nombre":"Mantequilla de Man\\u00ed Univalle 350 g","cantidad":10,"peso_unitario":"0.350","unidad_medida_id":null,"tipo_empaque_id":null,"precio_unitario":"0.00","total_peso":"3.500","total_precio":"0.00","created_at":"2025-12-12T21:23:09.000000Z","updated_at":"2025-12-12T21:23:09.000000Z","alto_producto_cm":null,"ancho_producto_cm":null,"largo_producto_cm":null,"producto_id":10}]},"qr_code":null}	2025-12-12 21:23:16	2025-12-12 21:23:16
15	11	28	10	ENV-251212-79EA93	success	\N	\N	{"success":true,"message":"Env\\u00edo creado exitosamente","data":{"codigo":"ENV-251212-79EA93","almacen_destino_id":2,"categoria":"general","fecha_creacion":"2025-12-12T00:00:00.000000Z","fecha_estimada_entrega":"2025-12-17T00:00:00.000000Z","hora_estimada":"14:00","estado":"pendiente","observaciones":"Pedido: PED-0011-20251212\\nCliente: jhair aguilar\\n\\n\\ud83d\\udccd UBICACI\\u00d3N DE RECOJO:\\nDirecci\\u00f3n: Calle Oruro, 621, Centro, Santa Cruz de la Sierra, Santa Cruz, Bolivia\\nReferencia: casa rosada\\nCoordenadas: -17.78942410, -63.17243653\\n\\nInstrucciones de entrega: NO\\nContacto: jhair cliente aguilar - Tel: 78113449\\nDirecci\\u00f3n de entrega: ALMACEN 1 (NO)","total_cantidad":10,"total_peso":"5.000","total_precio":"0.00","updated_at":"2025-12-12T21:47:35.000000Z","created_at":"2025-12-12T21:47:35.000000Z","id":10,"almacen_destino":{"id":2,"nombre":"Almac\\u00e9n Centro","usuario_almacen_id":1,"latitud":"-17.7890000","longitud":"-63.1800000","direccion_completa":"Calle Libertad, Santa Cruz","es_planta":false,"activo":true,"created_at":"2025-12-08T14:17:35.000000Z","updated_at":"2025-12-08T14:17:35.000000Z"},"productos":[{"id":8,"envio_id":10,"producto_nombre":"Pasta Integral Univalle 500 g","cantidad":10,"peso_unitario":"0.500","unidad_medida_id":null,"tipo_empaque_id":null,"precio_unitario":"0.00","total_peso":"5.000","total_precio":"0.00","created_at":"2025-12-12T21:47:35.000000Z","updated_at":"2025-12-12T21:47:35.000000Z","alto_producto_cm":null,"ancho_producto_cm":null,"largo_producto_cm":null,"producto_id":11}]},"qr_code":null}	2025-12-12 21:47:40	2025-12-12 21:47:40
16	12	30	\N	\N	failed	No hay almacenes disponibles en plantaCruds para la ubicación de recojo: Calle Campo Rosa del Sara, Estación Argentina, Santa Cruz de la Sierra, Santa Cruz, Bolivia	\N	\N	2025-12-13 03:52:18	2025-12-13 03:52:18
17	13	32	\N	\N	failed	No hay almacenes disponibles en plantaCruds para la ubicación de recojo: Calle Teniente Roca Peirano, Estación Argentina, Santa Cruz de la Sierra, Santa Cruz, Bolivia	\N	\N	2025-12-13 04:12:06	2025-12-13 04:12:06
18	15	36	\N	\N	failed	No hay almacenes de destino disponibles en plantaCruds para el destino: alm1	\N	\N	2025-12-14 13:08:52	2025-12-14 13:08:52
\.


--
-- TOC entry 5205 (class 0 OID 124632)
-- Dependencies: 277
-- Data for Name: solicitud_material; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.solicitud_material (solicitud_id, pedido_id, numero_solicitud, fecha_solicitud, fecha_requerida, observaciones, direccion, latitud, longitud) FROM stdin;
1	1	SOL-0001-20251210	2025-12-10	2025-12-11	\N	\N	\N	\N
2	2	SOL-0002-20251210	2025-12-10	2025-12-11	\N	\N	\N	\N
3	3	SOL-0003-20251210	2025-12-10	2025-12-11	\N	\N	\N	\N
4	4	SOL-0004-20251210	2025-12-10	2025-12-12	\N	\N	\N	\N
5	8	SOL-0005-20251211	2025-12-11	2025-12-13	\N	\N	\N	\N
6	7	SOL-0006-20251211	2025-12-11	2025-12-20	\N	\N	\N	\N
7	9	SOL-0007-20251211	2025-12-11	2025-12-18	\N	\N	\N	\N
8	5	SOL-0008-20251212	2025-12-12	2025-12-14	\N	\N	\N	\N
9	12	SOL-0009-20251213	2025-12-13	2025-12-14	\N	\N	\N	\N
10	13	SOL-0010-20251213	2025-12-13	2025-12-14	\N	\N	\N	\N
11	15	SOL-0011-20251214	2025-12-14	2025-12-15	\N	\N	\N	\N
12	16	SOL-0012-20251214	2025-12-14	2025-12-15	\N	planta	-17.81835647	-63.17376578
\.


--
-- TOC entry 5182 (class 0 OID 124308)
-- Dependencies: 254
-- Data for Name: tipo_movimiento; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipo_movimiento (tipo_movimiento_id, codigo, nombre, afecta_stock, es_entrada, activo) FROM stdin;
1	ENTRADA	Entrada de Material	t	t	t
2	SALIDA	Salida de Material	t	f	t
3	AJUSTE_INV	Ajuste de Inventario	t	f	t
4	CONSUMO	Consumo en Producción	t	f	t
5	DEVOLUCION	Devolución de Material	t	t	t
6	PERDIDA	Pérdida de Material	t	f	t
7	TRANSFERENCIA	Transferencia entre Almacenes	f	f	t
8	VENCIMIENTO	Material Vencido	t	f	t
\.


--
-- TOC entry 5181 (class 0 OID 124300)
-- Dependencies: 253
-- Data for Name: unidad_medida; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.unidad_medida (unidad_id, codigo, nombre, descripcion, activo) FROM stdin;
1	KG	Kilogramo	Unidad de masa en el Sistema Internacional	t
2	G	Gramo	Unidad de masa equivalente a una milésima de kilogramo	t
3	L	Litro	Unidad de volumen en el Sistema Internacional	t
4	ML	Mililitro	Unidad de volumen equivalente a una milésima de litro	t
5	M	Metro	Unidad de longitud en el Sistema Internacional	t
6	CM	Centímetro	Unidad de longitud equivalente a una centésima de metro	t
7	UN	Unidad	Unidad de conteo o pieza	t
8	M2	Metro Cuadrado	Unidad de área	t
9	M3	Metro Cúbico	Unidad de volumen	t
10	BOLSA	Bolsa	Unidad de empaque en bolsa	t
\.


--
-- TOC entry 5186 (class 0 OID 124346)
-- Dependencies: 258
-- Data for Name: variable_estandar; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.variable_estandar (variable_id, codigo, nombre, unidad, descripcion, activo) FROM stdin;
1	TEMPERATURA	Temperatura	°C	Temperatura del proceso	t
2	PRESION	Presión	PSI	Presión del proceso	t
3	VELOCIDAD	Velocidad	RPM	Velocidad de la máquina	t
4	TIEMPO	Tiempo de Proceso	min	Tiempo de duración del proceso	t
5	HUMEDAD	Humedad	%	Nivel de humedad	t
6	PH	pH	pH	Nivel de acidez/alcalinidad	t
7	PESO	Peso	kg	Peso del producto	t
8	VOLUMEN	Volumen	L	Volumen del producto	t
9	DENSIDAD	Densidad	g/cm³	Densidad del material	t
10	VISCOSIDAD	Viscosidad	cP	Viscosidad del fluido	t
12	COLOR	Color	Código	Código de color del producto	t
11	CALIDAD	Calidad Visual	Escala 1-10	Evaluación visual de calidad	t
\.


--
-- TOC entry 5201 (class 0 OID 124567)
-- Dependencies: 273
-- Data for Name: variable_proceso_maquina; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.variable_proceso_maquina (variable_id, proceso_maquina_id, variable_estandar_id, valor_minimo, valor_maximo, valor_objetivo, obligatorio) FROM stdin;
1	1	3	0.00	10.00	\N	t
2	2	2	0.00	100.00	\N	t
3	3	4	0.00	30.00	\N	t
\.


--
-- TOC entry 5238 (class 0 OID 0)
-- Dependencies: 240
-- Name: almacenaje_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.almacenaje_seq', 12, true);


--
-- TOC entry 5239 (class 0 OID 0)
-- Dependencies: 220
-- Name: categoria_materia_prima_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categoria_materia_prima_seq', 1, false);


--
-- TOC entry 5240 (class 0 OID 0)
-- Dependencies: 219
-- Name: cliente_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cliente_seq', 4, true);


--
-- TOC entry 5241 (class 0 OID 0)
-- Dependencies: 231
-- Name: destino_pedido_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.destino_pedido_seq', 26, true);


--
-- TOC entry 5242 (class 0 OID 0)
-- Dependencies: 242
-- Name: detalle_solicitud_material_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.detalle_solicitud_material_seq', 12, true);


--
-- TOC entry 5243 (class 0 OID 0)
-- Dependencies: 239
-- Name: evaluacion_final_proceso_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.evaluacion_final_proceso_seq', 12, true);


--
-- TOC entry 5244 (class 0 OID 0)
-- Dependencies: 234
-- Name: lote_materia_prima_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lote_materia_prima_seq', 13, true);


--
-- TOC entry 5245 (class 0 OID 0)
-- Dependencies: 233
-- Name: lote_produccion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lote_produccion_seq', 12, true);


--
-- TOC entry 5246 (class 0 OID 0)
-- Dependencies: 223
-- Name: maquina_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.maquina_seq', 9, true);


--
-- TOC entry 5247 (class 0 OID 0)
-- Dependencies: 226
-- Name: materia_prima_base_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.materia_prima_base_seq', 3, true);


--
-- TOC entry 5248 (class 0 OID 0)
-- Dependencies: 227
-- Name: materia_prima_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.materia_prima_seq', 12, true);


--
-- TOC entry 5249 (class 0 OID 0)
-- Dependencies: 244
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 10, true);


--
-- TOC entry 5250 (class 0 OID 0)
-- Dependencies: 225
-- Name: operador_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.operador_seq', 4, true);


--
-- TOC entry 5251 (class 0 OID 0)
-- Dependencies: 229
-- Name: pedido_cliente_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pedido_cliente_seq', 10, true);


--
-- TOC entry 5252 (class 0 OID 0)
-- Dependencies: 284
-- Name: pedido_documentos_entrega_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pedido_documentos_entrega_id_seq', 1, false);


--
-- TOC entry 5253 (class 0 OID 0)
-- Dependencies: 246
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permissions_id_seq', 21, true);


--
-- TOC entry 5254 (class 0 OID 0)
-- Dependencies: 236
-- Name: proceso_maquina_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.proceso_maquina_seq', 3, true);


--
-- TOC entry 5255 (class 0 OID 0)
-- Dependencies: 224
-- Name: proceso_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.proceso_seq', 11, true);


--
-- TOC entry 5256 (class 0 OID 0)
-- Dependencies: 232
-- Name: producto_destino_pedido_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.producto_destino_pedido_seq', 40, true);


--
-- TOC entry 5257 (class 0 OID 0)
-- Dependencies: 230
-- Name: producto_pedido_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.producto_pedido_seq', 25, true);


--
-- TOC entry 5258 (class 0 OID 0)
-- Dependencies: 228
-- Name: producto_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.producto_seq', 1, false);


--
-- TOC entry 5259 (class 0 OID 0)
-- Dependencies: 221
-- Name: proveedor_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.proveedor_seq', 1, true);


--
-- TOC entry 5260 (class 0 OID 0)
-- Dependencies: 235
-- Name: registro_movimiento_material_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.registro_movimiento_material_seq', 25, true);


--
-- TOC entry 5261 (class 0 OID 0)
-- Dependencies: 238
-- Name: registro_proceso_maquina_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.registro_proceso_maquina_seq', 13, true);


--
-- TOC entry 5262 (class 0 OID 0)
-- Dependencies: 243
-- Name: respuesta_proveedor_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.respuesta_proveedor_seq', 1, false);


--
-- TOC entry 5263 (class 0 OID 0)
-- Dependencies: 248
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 3, true);


--
-- TOC entry 5264 (class 0 OID 0)
-- Dependencies: 280
-- Name: seguimiento_envio_pedido_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seguimiento_envio_pedido_id_seq', 18, true);


--
-- TOC entry 5265 (class 0 OID 0)
-- Dependencies: 241
-- Name: solicitud_material_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.solicitud_material_seq', 12, true);


--
-- TOC entry 5266 (class 0 OID 0)
-- Dependencies: 218
-- Name: tipo_movimiento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_movimiento_seq', 1, false);


--
-- TOC entry 5267 (class 0 OID 0)
-- Dependencies: 217
-- Name: unidad_medida_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.unidad_medida_seq', 1, false);


--
-- TOC entry 5268 (class 0 OID 0)
-- Dependencies: 222
-- Name: variable_estandar_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.variable_estandar_seq', 13, true);


--
-- TOC entry 5269 (class 0 OID 0)
-- Dependencies: 237
-- Name: variable_proceso_maquina_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.variable_proceso_maquina_seq', 3, true);


--
-- TOC entry 4945 (class 2606 OID 124631)
-- Name: almacenaje almacenaje_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.almacenaje
    ADD CONSTRAINT almacenaje_pkey PRIMARY KEY (almacenaje_id);


--
-- TOC entry 4962 (class 2606 OID 124707)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- TOC entry 4960 (class 2606 OID 124700)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 4882 (class 2606 OID 124335)
-- Name: categoria_materia_prima categoria_materia_prima_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoria_materia_prima
    ADD CONSTRAINT categoria_materia_prima_codigo_unique UNIQUE (codigo);


--
-- TOC entry 4884 (class 2606 OID 124333)
-- Name: categoria_materia_prima categoria_materia_prima_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoria_materia_prima
    ADD CONSTRAINT categoria_materia_prima_pkey PRIMARY KEY (categoria_id);


--
-- TOC entry 4878 (class 2606 OID 124327)
-- Name: cliente cliente_nit_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cliente
    ADD CONSTRAINT cliente_nit_unique UNIQUE (nit);


--
-- TOC entry 4880 (class 2606 OID 124325)
-- Name: cliente cliente_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cliente
    ADD CONSTRAINT cliente_pkey PRIMARY KEY (cliente_id);


--
-- TOC entry 4924 (class 2606 OID 124484)
-- Name: destino_pedido destino_pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.destino_pedido
    ADD CONSTRAINT destino_pedido_pkey PRIMARY KEY (destino_id);


--
-- TOC entry 4951 (class 2606 OID 124662)
-- Name: detalle_solicitud_material detalle_solicitud_material_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalle_solicitud_material
    ADD CONSTRAINT detalle_solicitud_material_pkey PRIMARY KEY (detalle_id);


--
-- TOC entry 4943 (class 2606 OID 124618)
-- Name: evaluacion_final_proceso evaluacion_final_proceso_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluacion_final_proceso
    ADD CONSTRAINT evaluacion_final_proceso_pkey PRIMARY KEY (evaluacion_id);


--
-- TOC entry 4933 (class 2606 OID 124533)
-- Name: lote_materia_prima lote_materia_prima_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lote_materia_prima
    ADD CONSTRAINT lote_materia_prima_pkey PRIMARY KEY (lote_material_id);


--
-- TOC entry 4929 (class 2606 OID 124518)
-- Name: lote_produccion lote_produccion_codigo_lote_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lote_produccion
    ADD CONSTRAINT lote_produccion_codigo_lote_unique UNIQUE (codigo_lote);


--
-- TOC entry 4931 (class 2606 OID 124516)
-- Name: lote_produccion lote_produccion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lote_produccion
    ADD CONSTRAINT lote_produccion_pkey PRIMARY KEY (lote_id);


--
-- TOC entry 4894 (class 2606 OID 124363)
-- Name: maquina maquina_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maquina
    ADD CONSTRAINT maquina_codigo_unique UNIQUE (codigo);


--
-- TOC entry 4896 (class 2606 OID 124361)
-- Name: maquina maquina_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.maquina
    ADD CONSTRAINT maquina_pkey PRIMARY KEY (maquina_id);


--
-- TOC entry 4906 (class 2606 OID 124401)
-- Name: materia_prima_base materia_prima_base_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materia_prima_base
    ADD CONSTRAINT materia_prima_base_codigo_unique UNIQUE (codigo);


--
-- TOC entry 4908 (class 2606 OID 124399)
-- Name: materia_prima_base materia_prima_base_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materia_prima_base
    ADD CONSTRAINT materia_prima_base_pkey PRIMARY KEY (material_id);


--
-- TOC entry 4910 (class 2606 OID 124418)
-- Name: materia_prima materia_prima_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materia_prima
    ADD CONSTRAINT materia_prima_pkey PRIMARY KEY (materia_prima_id);


--
-- TOC entry 4852 (class 2606 OID 124240)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 4863 (class 2606 OID 124273)
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- TOC entry 4866 (class 2606 OID 124284)
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- TOC entry 4902 (class 2606 OID 124379)
-- Name: operador operador_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operador
    ADD CONSTRAINT operador_pkey PRIMARY KEY (operador_id);


--
-- TOC entry 4904 (class 2606 OID 124381)
-- Name: operador operador_usuario_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operador
    ADD CONSTRAINT operador_usuario_unique UNIQUE (usuario);


--
-- TOC entry 4916 (class 2606 OID 124452)
-- Name: pedido_cliente pedido_cliente_numero_pedido_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pedido_cliente
    ADD CONSTRAINT pedido_cliente_numero_pedido_unique UNIQUE (numero_pedido);


--
-- TOC entry 4918 (class 2606 OID 124450)
-- Name: pedido_cliente pedido_cliente_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pedido_cliente
    ADD CONSTRAINT pedido_cliente_pkey PRIMARY KEY (pedido_id);


--
-- TOC entry 4965 (class 2606 OID 132203)
-- Name: pedido_documentos_entrega pedido_documentos_entrega_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pedido_documentos_entrega
    ADD CONSTRAINT pedido_documentos_entrega_pkey PRIMARY KEY (id);


--
-- TOC entry 4854 (class 2606 OID 124251)
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 4856 (class 2606 OID 124249)
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- TOC entry 4898 (class 2606 OID 124371)
-- Name: proceso proceso_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proceso
    ADD CONSTRAINT proceso_codigo_unique UNIQUE (codigo);


--
-- TOC entry 4937 (class 2606 OID 124566)
-- Name: proceso_maquina proceso_maquina_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proceso_maquina
    ADD CONSTRAINT proceso_maquina_pkey PRIMARY KEY (proceso_maquina_id);


--
-- TOC entry 4900 (class 2606 OID 124369)
-- Name: proceso proceso_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proceso
    ADD CONSTRAINT proceso_pkey PRIMARY KEY (proceso_id);


--
-- TOC entry 4912 (class 2606 OID 124435)
-- Name: producto producto_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_codigo_unique UNIQUE (codigo);


--
-- TOC entry 4927 (class 2606 OID 124502)
-- Name: producto_destino_pedido producto_destino_pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto_destino_pedido
    ADD CONSTRAINT producto_destino_pedido_pkey PRIMARY KEY (producto_destino_id);


--
-- TOC entry 4921 (class 2606 OID 124471)
-- Name: producto_pedido producto_pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto_pedido
    ADD CONSTRAINT producto_pedido_pkey PRIMARY KEY (producto_pedido_id);


--
-- TOC entry 4914 (class 2606 OID 124433)
-- Name: producto producto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_pkey PRIMARY KEY (producto_id);


--
-- TOC entry 4886 (class 2606 OID 124345)
-- Name: proveedor proveedor_nit_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proveedor
    ADD CONSTRAINT proveedor_nit_unique UNIQUE (nit);


--
-- TOC entry 4888 (class 2606 OID 124343)
-- Name: proveedor proveedor_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proveedor
    ADD CONSTRAINT proveedor_pkey PRIMARY KEY (proveedor_id);


--
-- TOC entry 4935 (class 2606 OID 124551)
-- Name: registro_movimiento_material registro_movimiento_material_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.registro_movimiento_material
    ADD CONSTRAINT registro_movimiento_material_pkey PRIMARY KEY (registro_id);


--
-- TOC entry 4941 (class 2606 OID 124605)
-- Name: registro_proceso_maquina registro_proceso_maquina_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.registro_proceso_maquina
    ADD CONSTRAINT registro_proceso_maquina_pkey PRIMARY KEY (registro_id);


--
-- TOC entry 4953 (class 2606 OID 124680)
-- Name: respuesta_proveedor respuesta_proveedor_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.respuesta_proveedor
    ADD CONSTRAINT respuesta_proveedor_pkey PRIMARY KEY (respuesta_id);


--
-- TOC entry 4868 (class 2606 OID 124299)
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- TOC entry 4858 (class 2606 OID 124262)
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 4860 (class 2606 OID 124260)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 4958 (class 2606 OID 124690)
-- Name: seguimiento_envio_pedido seguimiento_envio_pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_envio_pedido
    ADD CONSTRAINT seguimiento_envio_pedido_pkey PRIMARY KEY (id);


--
-- TOC entry 4947 (class 2606 OID 124647)
-- Name: solicitud_material solicitud_material_numero_solicitud_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitud_material
    ADD CONSTRAINT solicitud_material_numero_solicitud_unique UNIQUE (numero_solicitud);


--
-- TOC entry 4949 (class 2606 OID 124645)
-- Name: solicitud_material solicitud_material_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitud_material
    ADD CONSTRAINT solicitud_material_pkey PRIMARY KEY (solicitud_id);


--
-- TOC entry 4874 (class 2606 OID 124317)
-- Name: tipo_movimiento tipo_movimiento_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_movimiento
    ADD CONSTRAINT tipo_movimiento_codigo_unique UNIQUE (codigo);


--
-- TOC entry 4876 (class 2606 OID 124315)
-- Name: tipo_movimiento tipo_movimiento_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_movimiento
    ADD CONSTRAINT tipo_movimiento_pkey PRIMARY KEY (tipo_movimiento_id);


--
-- TOC entry 4870 (class 2606 OID 124307)
-- Name: unidad_medida unidad_medida_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unidad_medida
    ADD CONSTRAINT unidad_medida_codigo_unique UNIQUE (codigo);


--
-- TOC entry 4872 (class 2606 OID 124305)
-- Name: unidad_medida unidad_medida_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unidad_medida
    ADD CONSTRAINT unidad_medida_pkey PRIMARY KEY (unidad_id);


--
-- TOC entry 4890 (class 2606 OID 124353)
-- Name: variable_estandar variable_estandar_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.variable_estandar
    ADD CONSTRAINT variable_estandar_codigo_unique UNIQUE (codigo);


--
-- TOC entry 4892 (class 2606 OID 124351)
-- Name: variable_estandar variable_estandar_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.variable_estandar
    ADD CONSTRAINT variable_estandar_pkey PRIMARY KEY (variable_id);


--
-- TOC entry 4939 (class 2606 OID 124582)
-- Name: variable_proceso_maquina variable_proceso_maquina_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.variable_proceso_maquina
    ADD CONSTRAINT variable_proceso_maquina_pkey PRIMARY KEY (variable_id);


--
-- TOC entry 4922 (class 1259 OID 124482)
-- Name: destino_pedido_pedido_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX destino_pedido_pedido_id_index ON public.destino_pedido USING btree (pedido_id);


--
-- TOC entry 4861 (class 1259 OID 124266)
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- TOC entry 4864 (class 1259 OID 124277)
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- TOC entry 4963 (class 1259 OID 132204)
-- Name: pedido_documentos_entrega_pedido_id_envio_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX pedido_documentos_entrega_pedido_id_envio_id_index ON public.pedido_documentos_entrega USING btree (pedido_id, envio_id);


--
-- TOC entry 4925 (class 1259 OID 124500)
-- Name: producto_destino_pedido_destino_id_producto_pedido_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX producto_destino_pedido_destino_id_producto_pedido_id_index ON public.producto_destino_pedido USING btree (destino_id, producto_pedido_id);


--
-- TOC entry 4919 (class 1259 OID 124469)
-- Name: producto_pedido_pedido_id_producto_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX producto_pedido_pedido_id_producto_id_index ON public.producto_pedido USING btree (pedido_id, producto_id);


--
-- TOC entry 4954 (class 1259 OID 124692)
-- Name: seguimiento_envio_pedido_destino_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX seguimiento_envio_pedido_destino_id_index ON public.seguimiento_envio_pedido USING btree (destino_id);


--
-- TOC entry 4955 (class 1259 OID 124693)
-- Name: seguimiento_envio_pedido_envio_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX seguimiento_envio_pedido_envio_id_index ON public.seguimiento_envio_pedido USING btree (envio_id);


--
-- TOC entry 4956 (class 1259 OID 124691)
-- Name: seguimiento_envio_pedido_pedido_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX seguimiento_envio_pedido_pedido_id_index ON public.seguimiento_envio_pedido USING btree (pedido_id);


--
-- TOC entry 4994 (class 2606 OID 124625)
-- Name: almacenaje almacenaje_lote_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.almacenaje
    ADD CONSTRAINT almacenaje_lote_id_foreign FOREIGN KEY (lote_id) REFERENCES public.lote_produccion(lote_id);


--
-- TOC entry 4978 (class 2606 OID 124477)
-- Name: destino_pedido destino_pedido_pedido_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.destino_pedido
    ADD CONSTRAINT destino_pedido_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedido_cliente(pedido_id) ON DELETE CASCADE;


--
-- TOC entry 4996 (class 2606 OID 124656)
-- Name: detalle_solicitud_material detalle_solicitud_material_material_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalle_solicitud_material
    ADD CONSTRAINT detalle_solicitud_material_material_id_foreign FOREIGN KEY (material_id) REFERENCES public.materia_prima_base(material_id);


--
-- TOC entry 4997 (class 2606 OID 124651)
-- Name: detalle_solicitud_material detalle_solicitud_material_solicitud_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalle_solicitud_material
    ADD CONSTRAINT detalle_solicitud_material_solicitud_id_foreign FOREIGN KEY (solicitud_id) REFERENCES public.solicitud_material(solicitud_id);


--
-- TOC entry 4993 (class 2606 OID 124612)
-- Name: evaluacion_final_proceso evaluacion_final_proceso_lote_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluacion_final_proceso
    ADD CONSTRAINT evaluacion_final_proceso_lote_id_foreign FOREIGN KEY (lote_id) REFERENCES public.lote_produccion(lote_id);


--
-- TOC entry 4982 (class 2606 OID 124522)
-- Name: lote_materia_prima lote_materia_prima_lote_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lote_materia_prima
    ADD CONSTRAINT lote_materia_prima_lote_id_foreign FOREIGN KEY (lote_id) REFERENCES public.lote_produccion(lote_id);


--
-- TOC entry 4983 (class 2606 OID 124527)
-- Name: lote_materia_prima lote_materia_prima_materia_prima_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lote_materia_prima
    ADD CONSTRAINT lote_materia_prima_materia_prima_id_foreign FOREIGN KEY (materia_prima_id) REFERENCES public.materia_prima(materia_prima_id);


--
-- TOC entry 4981 (class 2606 OID 124510)
-- Name: lote_produccion lote_produccion_pedido_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lote_produccion
    ADD CONSTRAINT lote_produccion_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedido_cliente(pedido_id);


--
-- TOC entry 4970 (class 2606 OID 124388)
-- Name: materia_prima_base materia_prima_base_categoria_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materia_prima_base
    ADD CONSTRAINT materia_prima_base_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES public.categoria_materia_prima(categoria_id);


--
-- TOC entry 4971 (class 2606 OID 124393)
-- Name: materia_prima_base materia_prima_base_unidad_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materia_prima_base
    ADD CONSTRAINT materia_prima_base_unidad_id_foreign FOREIGN KEY (unidad_id) REFERENCES public.unidad_medida(unidad_id);


--
-- TOC entry 4972 (class 2606 OID 124407)
-- Name: materia_prima materia_prima_material_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materia_prima
    ADD CONSTRAINT materia_prima_material_id_foreign FOREIGN KEY (material_id) REFERENCES public.materia_prima_base(material_id);


--
-- TOC entry 4973 (class 2606 OID 124412)
-- Name: materia_prima materia_prima_proveedor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materia_prima
    ADD CONSTRAINT materia_prima_proveedor_id_foreign FOREIGN KEY (proveedor_id) REFERENCES public.proveedor(proveedor_id);


--
-- TOC entry 4966 (class 2606 OID 124267)
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 4967 (class 2606 OID 124278)
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 4975 (class 2606 OID 124444)
-- Name: pedido_cliente pedido_cliente_cliente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pedido_cliente
    ADD CONSTRAINT pedido_cliente_cliente_id_foreign FOREIGN KEY (cliente_id) REFERENCES public.cliente(cliente_id);


--
-- TOC entry 4986 (class 2606 OID 124560)
-- Name: proceso_maquina proceso_maquina_maquina_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proceso_maquina
    ADD CONSTRAINT proceso_maquina_maquina_id_foreign FOREIGN KEY (maquina_id) REFERENCES public.maquina(maquina_id);


--
-- TOC entry 4987 (class 2606 OID 124555)
-- Name: proceso_maquina proceso_maquina_proceso_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proceso_maquina
    ADD CONSTRAINT proceso_maquina_proceso_id_foreign FOREIGN KEY (proceso_id) REFERENCES public.proceso(proceso_id);


--
-- TOC entry 4979 (class 2606 OID 124490)
-- Name: producto_destino_pedido producto_destino_pedido_destino_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto_destino_pedido
    ADD CONSTRAINT producto_destino_pedido_destino_id_foreign FOREIGN KEY (destino_id) REFERENCES public.destino_pedido(destino_id) ON DELETE CASCADE;


--
-- TOC entry 4980 (class 2606 OID 124495)
-- Name: producto_destino_pedido producto_destino_pedido_producto_pedido_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto_destino_pedido
    ADD CONSTRAINT producto_destino_pedido_producto_pedido_id_foreign FOREIGN KEY (producto_pedido_id) REFERENCES public.producto_pedido(producto_pedido_id) ON DELETE CASCADE;


--
-- TOC entry 4976 (class 2606 OID 124459)
-- Name: producto_pedido producto_pedido_pedido_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto_pedido
    ADD CONSTRAINT producto_pedido_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedido_cliente(pedido_id) ON DELETE CASCADE;


--
-- TOC entry 4977 (class 2606 OID 124464)
-- Name: producto_pedido producto_pedido_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto_pedido
    ADD CONSTRAINT producto_pedido_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.producto(producto_id);


--
-- TOC entry 4974 (class 2606 OID 124427)
-- Name: producto producto_unidad_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_unidad_id_foreign FOREIGN KEY (unidad_id) REFERENCES public.unidad_medida(unidad_id);


--
-- TOC entry 4984 (class 2606 OID 124540)
-- Name: registro_movimiento_material registro_movimiento_material_material_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.registro_movimiento_material
    ADD CONSTRAINT registro_movimiento_material_material_id_foreign FOREIGN KEY (material_id) REFERENCES public.materia_prima_base(material_id);


--
-- TOC entry 4985 (class 2606 OID 124545)
-- Name: registro_movimiento_material registro_movimiento_material_tipo_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.registro_movimiento_material
    ADD CONSTRAINT registro_movimiento_material_tipo_movimiento_id_foreign FOREIGN KEY (tipo_movimiento_id) REFERENCES public.tipo_movimiento(tipo_movimiento_id);


--
-- TOC entry 4990 (class 2606 OID 124589)
-- Name: registro_proceso_maquina registro_proceso_maquina_lote_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.registro_proceso_maquina
    ADD CONSTRAINT registro_proceso_maquina_lote_id_foreign FOREIGN KEY (lote_id) REFERENCES public.lote_produccion(lote_id);


--
-- TOC entry 4991 (class 2606 OID 124599)
-- Name: registro_proceso_maquina registro_proceso_maquina_operador_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.registro_proceso_maquina
    ADD CONSTRAINT registro_proceso_maquina_operador_id_foreign FOREIGN KEY (operador_id) REFERENCES public.operador(operador_id);


--
-- TOC entry 4992 (class 2606 OID 124594)
-- Name: registro_proceso_maquina registro_proceso_maquina_proceso_maquina_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.registro_proceso_maquina
    ADD CONSTRAINT registro_proceso_maquina_proceso_maquina_id_foreign FOREIGN KEY (proceso_maquina_id) REFERENCES public.proceso_maquina(proceso_maquina_id);


--
-- TOC entry 4998 (class 2606 OID 124674)
-- Name: respuesta_proveedor respuesta_proveedor_proveedor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.respuesta_proveedor
    ADD CONSTRAINT respuesta_proveedor_proveedor_id_foreign FOREIGN KEY (proveedor_id) REFERENCES public.proveedor(proveedor_id);


--
-- TOC entry 4999 (class 2606 OID 124669)
-- Name: respuesta_proveedor respuesta_proveedor_solicitud_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.respuesta_proveedor
    ADD CONSTRAINT respuesta_proveedor_solicitud_id_foreign FOREIGN KEY (solicitud_id) REFERENCES public.solicitud_material(solicitud_id);


--
-- TOC entry 4968 (class 2606 OID 124288)
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 4969 (class 2606 OID 124293)
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 4995 (class 2606 OID 124639)
-- Name: solicitud_material solicitud_material_pedido_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitud_material
    ADD CONSTRAINT solicitud_material_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedido_cliente(pedido_id);


--
-- TOC entry 4988 (class 2606 OID 124571)
-- Name: variable_proceso_maquina variable_proceso_maquina_proceso_maquina_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.variable_proceso_maquina
    ADD CONSTRAINT variable_proceso_maquina_proceso_maquina_id_foreign FOREIGN KEY (proceso_maquina_id) REFERENCES public.proceso_maquina(proceso_maquina_id);


--
-- TOC entry 4989 (class 2606 OID 124576)
-- Name: variable_proceso_maquina variable_proceso_maquina_variable_estandar_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.variable_proceso_maquina
    ADD CONSTRAINT variable_proceso_maquina_variable_estandar_id_foreign FOREIGN KEY (variable_estandar_id) REFERENCES public.variable_estandar(variable_id);


-- Completed on 2025-12-14 10:38:04

--
-- PostgreSQL database dump complete
--

