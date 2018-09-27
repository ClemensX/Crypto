package de.fehrprice.crypto;

import java.math.BigInteger;

public class Ed25519 extends Curve25519 {

	public static BigInteger q;
	public static BigInteger q_minus2;
	public static BigInteger Bx;
	public static BigInteger By;
	public static BigInteger d;
	public static BigInteger I;

	public class KeyPair {
		public String privateKey;
		public String publicKey;
	}

	public Ed25519() {
		super();
		q = this.p;
		q_minus2 = this.p_minus2;
		d = BigInteger.valueOf(-121665).multiply(inv(BigInteger.valueOf(121666)));
		d = d.mod(q);
		I = expmod(BigInteger.valueOf(2), q.subtract(BigInteger.ONE).divide(BigInteger.valueOf(4)), q);
		By = BigInteger.valueOf(4).multiply(inv(BigInteger.valueOf(5)));
		By = By.mod(q);
		Bx = xrecover(By);
		Bx = Bx.mod(q);
	}
	
	/**
	 * @param secretKeyString secretkey must be 64 byte hex string or null/empty
	 * @return
	 */
	public KeyPair keygen(String secretKeyString) {
		RSA rsa = new RSA();
		AES aes = new AES();
		KeyPair keys = new KeyPair();
		if (secretKeyString == null) {
			secretKeyString = "";
		}
		int len = secretKeyString.length();
		if (len != 0 && len != 64) {
			throw new AssertionError("keylen invalid (must be 32 bytes): " + secretKeyString);
		}
		if (len == 0) {
			// first we need a public key: (no need for prime, just a random number):
			byte[] privKeyBytes = aes.random(32);
			// convert to hex string
			secretKeyString = aes.toString(privKeyBytes);
			System.out.println("priv key generated: " + secretKeyString);
		}
//		  h = H(sk)
//		  a = 2**(b-2) + sum(2**i * bit(h,i) for i in range(3,b-2))
//		  A = scalarmult(B,a)

//		khash=self.H(privkey,None,None)
//		a=from_le(self.__clamp(khash[:self.b//8]))
//		#Return the key pair (public key is A=Enc(aB).
//		return privkey,(self.B*a).encode()
		
		byte[] sk = aes.toByteArray(secretKeyString);
		byte[] h = this.h(sk);
		BigInteger a = this.decodeScalar25519(h);
		System.out.println("a = " + a);
		// a * B
		// B = [Bx % q,By % q]
		//By = 4 * inv(5)
		//Bx = xrecover(By)
		BigInteger B[] = new BigInteger[2];
		B[0] = Bx;
		B[1] = By;
		BigInteger A[] = scalarmult(B, a);
		System.out.println("A[0]: " + A[0]);
		System.out.println("A[1]: " + A[1]);
		BigInteger encoded = encodepoint(A);
		System.out.println("encoded: " + encoded);
		System.out.println("encoded: " + asLittleEndianHexString(encoded));
		
		System.out.println("encoded: " + encoded.toString(16));
		return keys;
	}
	
	private BigInteger encodepoint(BigInteger[] a) {
		byte[] encoded = decodeFromBigIntegerLittleEndian(a[1]);
		if (a[0].testBit(0)) {
			// set highest bit in lowest byte
			encoded[31] = (byte)(((int)encoded[31]) | 0x80);
		}
		return decodeLittleEndian(encoded, 255);
	}

	private BigInteger[] scalarmult(BigInteger[] P, BigInteger e) {
		if (e.equals(BigInteger.ZERO)) {
			BigInteger r[] = new BigInteger[2];
			r[0] = BigInteger.ZERO;
			r[1] = BigInteger.ONE;
			return r;
		}
		BigInteger Q[] = scalarmult(P, e.divide(BigInteger.valueOf(2)));
		Q = edwards(Q, Q);
		if (e.testBit(0)) {
			Q = edwards(Q, P);
		}
		return Q;
	}

	private BigInteger[] edwards(BigInteger[] P, BigInteger[] Q) {
		BigInteger r[] = new BigInteger[2];
		//x1 = P[0]
		//y1 = P[1]
		//x2 = Q[0]
		//y2 = Q[1]
		//x3 = (x1*y2+x2*y1) * inv(1+d*x1*x2*y1*y2)
		//y3 = (y1*y2+x1*x2) * inv(1-d*x1*x2*y1*y2)
		//return [x3 % q,y3 % q]
		BigInteger x1 = P[0];
		BigInteger y1 = P[1];
		BigInteger x2 = Q[0];
		BigInteger y2 = Q[1];
		BigInteger m = d.multiply(x1).multiply(x2).multiply(y1).multiply(y2);
		BigInteger x3 = x1.multiply(y2).add(x2.multiply(y1));
		x3 = x3.multiply(inv(BigInteger.ONE.add(m)));
		BigInteger y3 = y1.multiply(y2).add(x1.multiply(x2));
		y3 = y3.multiply(inv(BigInteger.ONE.subtract(m)));
		r[0] = x3.mod(q);
		r[1] = y3.mod(q);
		return r;
	}

	private BigInteger xrecover(BigInteger y) {
		BigInteger y_squared = y.multiply(y);
		BigInteger xx = y_squared.subtract(BigInteger.ONE);
		BigInteger t = d.multiply(y_squared).add(BigInteger.ONE);
		t = inv(t);
		xx = xx.multiply(t);
		BigInteger q38 = q.add(BigInteger.valueOf(3)).divide(BigInteger.valueOf(8));
		BigInteger x = expmod(xx, q38, q);
		BigInteger x_squared = x.multiply(x);
		if (!x_squared.subtract(xx).mod(q).equals(BigInteger.ZERO)) {
			x = x.multiply(I).mod(q);
		}
		if (!x.mod(BigInteger.valueOf(2)).equals(BigInteger.ZERO)) {
			x = q.subtract(x);
		}
		return x;
	}

	private BigInteger expmod(BigInteger b, BigInteger e, BigInteger m) {
		return b.modPow(e, m);
	}

	private BigInteger inv(BigInteger b) {
		return expmod(b, q_minus2, q);
	}
	
	private byte[] h(byte[] message) {
		SHA sha = new SHA();
		byte[] digest = sha.sha512(message);
		// we need only lower 32 bytes
		byte[] ret = new byte[32];
		System.arraycopy(digest, 32, ret, 0, 32);
		return ret;
	}

	public String publicKey(String secretKeyString) {
		return keygen(secretKeyString).publicKey;
	}
	
}
