import * as THREE from 'three';

class LandingScene {
  constructor() {
    this.canvas = document.getElementById('three-canvas');
    if (!this.canvas) return;

    this.mouse = { x: 0, y: 0 };
    this.target = { x: 0, y: 0 };
    this.clock = new THREE.Clock();

    this.renderer = new THREE.WebGLRenderer({ canvas: this.canvas, alpha: true, antialias: true });
    this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    this.renderer.outputColorSpace = THREE.SRGBColorSpace;

    this.scene = new THREE.Scene();
    this.camera = new THREE.PerspectiveCamera(45, 2, 0.1, 100);
    this.camera.position.set(0, 0, 12);
    this.camera.lookAt(0, 0, 0);

    this.addLights();
    this.createCentralOrb();
    this.createOrbitingShapes();
    this.createParticles();
    this.createConnectionLines();

    this.resize();
    this.setupEvents();
    this.animate();
  }

  addLights() {
    this.scene.add(new THREE.AmbientLight('#2a4a7f', 1.2));

    const key = new THREE.DirectionalLight('#6eb5ff', 4.5);
    key.position.set(5, 8, 5);
    this.scene.add(key);

    const fill = new THREE.DirectionalLight('#7c3aed', 1.8);
    fill.position.set(-6, -2, 2);
    this.scene.add(fill);

    const rim = new THREE.DirectionalLight('#0f766e', 2.5);
    rim.position.set(0, 0, -6);
    this.scene.add(rim);
  }

  createCentralOrb() {
    const geo = new THREE.IcosahedronGeometry(1.1, 3);
    const mat = new THREE.MeshPhysicalMaterial({
      color: '#2563eb',
      metalness: 0.05,
      roughness: 0.2,
      transparent: true,
      opacity: 0.18,
      envMapIntensity: 0.4,
    });
    this.centralOrb = new THREE.Mesh(geo, mat);
    this.scene.add(this.centralOrb);

    const wireGeo = new THREE.IcosahedronGeometry(1.25, 2);
    const wireMat = new THREE.MeshBasicMaterial({
      color: '#6eb5ff',
      wireframe: true,
      transparent: true,
      opacity: 0.13,
    });
    this.wireOrb = new THREE.Mesh(wireGeo, wireMat);
    this.scene.add(this.wireOrb);

    const ringGeo = new THREE.TorusGeometry(1.6, 0.015, 16, 100);
    const ringMat = new THREE.MeshStandardMaterial({
      color: '#60a5fa',
      roughness: 0.3,
      metalness: 0.5,
      emissive: '#1e40af',
      emissiveIntensity: 0.3,
    });
    this.rings = [];
    for (let i = 0; i < 3; i++) {
      const ring = new THREE.Mesh(ringGeo, ringMat);
      ring.rotation.x = Math.PI / 2 + (i * Math.PI / 3);
      ring.rotation.y = i * Math.PI / 4;
      this.rings.push(ring);
      this.scene.add(ring);
    }
  }

  createOrbitingShapes() {
    this.shapes = [];
    const colors = ['#0f766e', '#7c3aed', '#d97706', '#e11d48', '#0891b2', '#2563eb'];
    const geometries = [
      new THREE.BoxGeometry(0.25, 0.25, 0.25),
      new THREE.SphereGeometry(0.15, 16, 16),
      new THREE.TetrahedronGeometry(0.18),
      new THREE.OctahedronGeometry(0.16),
      new THREE.ConeGeometry(0.14, 0.28, 6),
      new THREE.TorusGeometry(0.14, 0.055, 8, 12),
    ];

    for (let i = 0; i < 9; i++) {
      const geo = geometries[i % geometries.length];
      const color = colors[i % colors.length];
      const mat = new THREE.MeshStandardMaterial({
        color,
        roughness: 0.25,
        metalness: 0.3,
        emissive: color,
        emissiveIntensity: 0.15,
      });

      const mesh = new THREE.Mesh(geo, mat);
      mesh.userData = {
        orbitRadius: 3.5 + Math.random() * 3.5,
        orbitSpeed: 0.15 + Math.random() * 0.35,
        orbitPhase: Math.random() * Math.PI * 2,
        orbitTilt: (Math.random() - 0.5) * 1.2,
        verticalAmp: 0.6 + Math.random() * 1.4,
        verticalSpeed: 0.3 + Math.random() * 0.5,
        spinSpeed: (Math.random() - 0.5) * 1.8,
      };
      mesh.position.set(
        Math.cos(mesh.userData.orbitPhase) * mesh.userData.orbitRadius,
        (Math.random() - 0.5) * 3,
        Math.sin(mesh.userData.orbitPhase) * mesh.userData.orbitRadius
      );
      this.shapes.push(mesh);
      this.scene.add(mesh);
    }
  }

  createParticles() {
    const count = 200;
    const positions = new Float32Array(count * 3);
    const sizes = new Float32Array(count);
    for (let i = 0; i < count; i++) {
      positions[i * 3] = (Math.random() - 0.5) * 14;
      positions[i * 3 + 1] = (Math.random() - 0.5) * 9;
      positions[i * 3 + 2] = (Math.random() - 0.5) * 10;
      sizes[i] = Math.random() * 3.5 + 0.8;
    }

    const geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geo.setAttribute('size', new THREE.BufferAttribute(sizes, 1));

    const mat = new THREE.PointsMaterial({
      color: '#8199c9',
      size: 0.04,
      blending: THREE.AdditiveBlending,
      depthWrite: false,
      transparent: true,
      opacity: 0.6,
    });

    this.particles = new THREE.Points(geo, mat);
    this.scene.add(this.particles);
  }

  createConnectionLines() {
    const points = [];
    const angles = [0, Math.PI * 0.4, Math.PI * 0.7, Math.PI * 1.3, Math.PI * 1.8, Math.PI * 2.2];
    for (const angle of angles) {
      const r = 1.45;
      points.push(new THREE.Vector3(Math.cos(angle) * r, Math.sin(angle * 1.6) * 1.0, Math.sin(angle) * r));
    }

    const curve = new THREE.CatmullRomCurve3(points, true);
    const tubeGeo = new THREE.TubeGeometry(curve, 80, 0.012, 6, true);
    const tubeMat = new THREE.MeshBasicMaterial({
      color: '#6788c4',
      transparent: true,
      opacity: 0.28,
      depthWrite: false,
    });
    this.tube = new THREE.Mesh(tubeGeo, tubeMat);
    this.scene.add(this.tube);
  }

  resize() {
    const el = this.canvas.parentElement;
    const w = el.clientWidth;
    const h = el.clientHeight;
    this.renderer.setSize(w, h, false);
    this.camera.aspect = w / h;
    this.camera.updateProjectionMatrix();
  }

  setupEvents() {
    this.resizeObserver = new ResizeObserver(() => this.resize());
    this.resizeObserver.observe(this.canvas.parentElement);

    document.addEventListener('mousemove', (e) => {
      this.mouse.x = (e.clientX / window.innerWidth) * 2 - 1;
      this.mouse.y = -(e.clientY / window.innerHeight) * 2 + 1;
    });

    document.addEventListener('touchmove', (e) => {
      if (e.touches.length) {
        this.mouse.x = (e.touches[0].clientX / window.innerWidth) * 2 - 1;
        this.mouse.y = -(e.touches[0].clientY / window.innerHeight) * 2 + 1;
      }
    }, { passive: true });
  }

  animate() {
    this.animId = requestAnimationFrame(() => this.animate());

    const dt = Math.min(this.clock.getDelta(), 0.1);
    const t = this.clock.elapsedTime;

    this.target.x += (this.mouse.x - this.target.x) * 0.04;
    this.target.y += (this.mouse.y - this.target.y) * 0.04;

    if (this.centralOrb) {
      this.centralOrb.rotation.y += dt * 0.12;
      this.centralOrb.rotation.x += dt * 0.06;
    }
    if (this.wireOrb) {
      this.wireOrb.rotation.y -= dt * 0.18;
      this.wireOrb.rotation.x -= dt * 0.08;
    }

    for (const ring of this.rings) {
      ring.rotation.z += dt * 0.1;
      ring.rotation.y += dt * 0.15;
    }

    for (const shape of this.shapes) {
      const d = shape.userData;
      const angle = d.orbitPhase + t * d.orbitSpeed;
      const r = d.orbitRadius;
      shape.position.x = Math.cos(angle) * r;
      shape.position.z = Math.sin(angle) * r;
      shape.position.y = Math.sin(t * d.verticalSpeed + d.orbitPhase) * d.verticalAmp;
      shape.rotation.x += dt * d.spinSpeed;
      shape.rotation.y += dt * d.spinSpeed * 0.7;
    }

    if (this.particles) {
      this.particles.rotation.y += dt * 0.04;
      this.particles.rotation.x += dt * 0.02;
    }

    if (this.tube) {
      this.tube.rotation.y += dt * 0.08;
      this.tube.rotation.x += dt * 0.04;
    }

    this.camera.position.x += (this.target.x * 1.4 - this.camera.position.x) * 0.03;
    this.camera.position.y += (-this.target.y * 0.6 - this.camera.position.y) * 0.03;
    this.camera.lookAt(0, 0, 0);

    this.renderer.render(this.scene, this.camera);
  }

  destroy() {
    if (this.animId) cancelAnimationFrame(this.animId);
    if (this.resizeObserver) this.resizeObserver.disconnect();
    this.renderer.dispose();
    this.scene.clear();
  }
}

document.addEventListener('DOMContentLoaded', () => new LandingScene());
