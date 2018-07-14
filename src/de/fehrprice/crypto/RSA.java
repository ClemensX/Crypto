package de.fehrprice.crypto;

import java.math.BigInteger;
import java.util.Arrays;
import java.util.Collections;
import java.util.List;

public class RSA {

	/**
	 * key implementation details see: http://www.di-mgt.com.au/crt_rsa.html
	 * bitcount must be multiple of 8 (to make primes fit in byte boundaries)
	 * http://tools.ietf.org/html/rfc3447#page-6
	 *
	 */
	public class KeyPair {
		
		public BigInteger p, q, n, dP, dQ, qInv, phi, e, d;
		// public key is (n, e) private key is (n, d). 
		// n is the big multiply (public), e is fixed to 65537
		
	}

	private static final int Certainty = 20;  // false positive 1 in a million

	public KeyPair keys = new KeyPair();
	
	private static final BigInteger global_e = BigInteger.valueOf(65537L);
	
	public void generateKeys(int bitcount) {
		if (bitcount % 16 != 0 || bitcount < 16) {
			throw new NumberFormatException("key bitcount not multiple of 16 or too small");
		}
		int n_bitlen = 0;
		BigInteger gcd;
		keys.e = global_e;
		do {
			keys.p = getRandomPrime(bitcount/2);
			keys.q = getRandomPrime(bitcount/2);
			if (keys.p.compareTo(keys.q) == -1) {
				// switch if p < q
				BigInteger x = keys.p;
				keys.p = keys.q;
				keys.q = x;
			}
			keys.n = keys.p.multiply(keys.q);
			n_bitlen = keys.n.bitLength();
			//keys.n = keys.p.multiply(keys.q);
			keys.phi = keys.p.subtract(BigInteger.ONE).multiply(keys.q.subtract(BigInteger.ONE));
			gcd = keys.phi.gcd(keys.e);
		} while (n_bitlen < bitcount && gcd.compareTo(BigInteger.ONE) == 0);
		keys.d = keys.e.modInverse(keys.phi);
		// public key is (n, e) private key is (n, d). 
		
//		System.out.println("found prime p: " + keys.p.toString(16));
//		System.out.println("found prime q: " + keys.q.toString(16));
//		System.out.println(" bitlen: " + keys.n.bitLength() + " n: " + keys.n.toString(16));
//		System.out.println("phi: " + keys.phi.toString(16) + " gcd: " + keys.phi.gcd(keys.e));
//		System.out.println("e: " + keys.e.toString(16) + " prime: " + keys.e.isProbablePrime(100));
//		System.out.println("d: " + keys.d.toString(16));
	}

	private BigInteger getRandomPrime(int bitcount) {
		for (int i = 0; i < 100; i++) {
			BigInteger prime = getRandomPrimeSingle(bitcount);
			if (prime != null) return prime; 
		}
		throw new NullPointerException("Could not find prime in 100 tries. Giving up.");
	}
	
	private BigInteger getRandomPrimeSingle(int bitcount) {
		AES aes = new AES();
		int bytesNeeded = bitcount/8;
		byte[] random = aes.random(bytesNeeded);
		while (((random[0]&0xff) & 0x80) == 0) {	// we want highest bit set to ensure big enough number
			random = aes.random(bytesNeeded);
		}
		BigInteger test = new BigInteger(aes.toString(random), 16);
		//System.out.println(" my num: " + aes.toString(random) + " BigInteger: " + test.toString(16));
		//System.out.println(" BigInteger bitlen: " + test.bitLength());
		// test at most 100 numbers to get prime, then give up
		for (int i = 1; i < 100; i+=2) {
			//System.out.println(" my num: " + aes.toString(random) + " BigInteger: " + test.toString(16));
			test = test.add(BigInteger.ONE).add(BigInteger.ONE);
			if (test.bitLength() != bitcount) {
				//System.out.println(" bitlength changed: " + test.bitLength());
				return getRandomPrime(bitcount);
			}
			if (test.isProbablePrime(Certainty)) {
				return test;
			}
		}
		//System.out.println(" prime not found in loop");
		return null;
	}
	
	/**
	 * Encrypt with public key
	 * @param string
	 * @return
	 */
	@Deprecated
	public BigInteger encrypt(String string) {
		return encrypt(string.getBytes());
	}
	
	/**
	 * Encrypt with public key
	 * @param r
	 * @return
	 */
	public String decrypt(BigInteger r) {
		String res = new String(decryptToByteArray(r));
		//System.out.println("decrypt: " + res);
		return res;
	}
	
	@Deprecated
	public BigInteger encrypt(byte[] message) {
		BigInteger m = encodeToBigInteger(message);
		if (m.compareTo(keys.n) > 0) {
			throw new NumberFormatException((message.length * 8) + " bit message too big. Should be <= " + (keys.n.bitLength()-1));
		}
		BigInteger r = m.modPow(keys.e, keys.n);// m°e mod n
		//System.out.println("encrypt: " + r.toString(16));
		return r;
	}
	
	public byte[] decryptToByteArray(BigInteger r) {
		BigInteger decrypt = r.modPow(keys.d, keys.n);
		byte[] decoded = decodeFromBigInteger(decrypt);
		return decoded;
	}

	public BigInteger encodeToBigInteger(byte[] message) {
		byte[] encoded = new byte[message.length + 1];
		encoded[0] = 01;
		System.arraycopy(message, 0, encoded, 1, message.length);
		return new BigInteger(encoded);
	}

	public byte[] decodeFromBigInteger(BigInteger m) {
		byte[] encoded = m.toByteArray();
		byte[] decoded = new byte[encoded.length-1];
		System.arraycopy(encoded, 1, decoded, 0, decoded.length);
		return decoded;
	}

	
	public byte[] decodeFromBigIntegerLittleEndian(BigInteger m) {
		byte[] encoded = m.toByteArray();
		// reverse order:
		byte[] encoded2 = new byte[encoded.length];
		for( int i = 0; i < encoded.length; i++) {
			encoded2[encoded.length-1-i] = encoded[i];
		}
		byte[] decoded = new byte[encoded2.length];
		System.arraycopy(encoded2, 0, decoded, 0, decoded.length);
		return decoded;
	}
	
	// public exponent to use
    private BigInteger publicExponent;
    
    // size of the key to generate, >= RSAKeyFactory.MIN_MODLEN
    private int keySize;
    
    // PRNG to use
    //private SecureRandom random;
 	public KeyPair generateKeyPairOLD() {
		// accomodate odd key sizes in case anybody wants to use them
		int lp = (keySize + 1) >> 1;
		int lq = keySize - lp;
//		if (random == null) {
//			random = JCAUtil.getSecureRandom();
//		}
//		BigInteger e = publicExponent;
//		while (true) {
//			// generate two random primes of size lp/lq
//			BigInteger p = BigInteger.probablePrime(lp, random);
//			BigInteger q, n;
//			do {
//				q = BigInteger.probablePrime(lq, random);
//				// convention is for p > q
//				if (p.compareTo(q) < 0) {
//					BigInteger tmp = p;
//					p = q;
//					q = tmp;
//				}
//				// modulus n = p * q
//				n = p.multiply(q);
//				// even with correctly sized p and q, there is a chance that
//				// n will be one bit short. re-generate the smaller prime if so
//			} while (n.bitLength() < keySize);
//
//			// phi = (p - 1) * (q - 1) must be relative prime to e
//			// otherwise RSA just won't work ;-)
//			BigInteger p1 = p.subtract(BigInteger.ONE);
//			BigInteger q1 = q.subtract(BigInteger.ONE);
//			BigInteger phi = p1.multiply(q1);
			// generate new p and q until they work. typically
			// the first try will succeed when using F4
//			if (e.gcd(phi).equals(BigInteger.ONE) == false) {
//				continue;
//			}

			// private exponent d is the inverse of e mod phi
//			BigInteger d = e.modInverse(phi);
//
//			// 1st prime exponent pe = d mod (p - 1)
//			BigInteger pe = d.mod(p1);
//			// 2nd prime exponent qe = d mod (q - 1)
//			BigInteger qe = d.mod(q1);
//
//			// crt coefficient coeff is the inverse of q mod p
//			BigInteger coeff = q.modInverse(p);
//
//			try {
//				//PublicKey publicKey = new RSAPublicKeyImpl(n, e);
//				//PrivateKey privateKey = new RSAPrivateCrtKeyImpl(n, e, d, p, q,	pe, qe, coeff);
//				return new KeyPair(n, e, d, p, q,	pe, qe, coeff);
//			} catch(Exception ex){}
//		}
//	}
		return null;
 	}

 	public static BigInteger hexStringToBigInteger(String hexString) {
//		RSA rsa = new RSA();
//		AES aes = new AES();
//		byte[] bytes = aes.toByteArray(hexString);
//		return rsa.encodeToBigInteger(bytes);
 		return new BigInteger(hexString, 16);
 	}
 	
 	public static String bigIntegerToHexString(BigInteger big) {
//		RSA rsa = new RSA();
//		AES aes = new AES();
//		return aes.toString(rsa.decodeFromBigInteger(big));
 		return big.toString(16);
 	}
 	
	/**
	 * Encrypt message with private key.
	 * All input and output is with hex strings!
	 * @param message
	 * @param private_d 
	 * @param alice_private
	 * @return
	 */
	public static String encryptMessageWithPrivateKey(String message, String private_d, String public_n) {
		AES aes = new AES();
		RSA rsa = new RSA();
		rsa.keys.n = hexStringToBigInteger(public_n);
		rsa.keys.d = hexStringToBigInteger(private_d);
		BigInteger m = hexStringToBigInteger(message);

		if (m.compareTo(rsa.keys.n) > 0) {
			throw new NumberFormatException((message.length()/2 * 8) + " bit message too big. Should be <= " + (rsa.keys.n.bitLength()-1));
		}
		BigInteger r = m.modPow(rsa.keys.d, rsa.keys.n);
		System.out.println("encrypt: " + r.toString(16));
		return bigIntegerToHexString(r);
	}

	/**
	 * Decrypt message with public key.
	 * All input and output is with hex strings!
	 * @param encryptedHex
	 * @param publicKey
	 * @return
	 */
	public static String decryptMessageWithPublicKey(String encryptedHexMsg, String publicKey) {
		AES aes = new AES();
		RSA rsa = new RSA();
		BigInteger c = hexStringToBigInteger(encryptedHexMsg);
		System.out.println("dec->enc: " + c.toString(16));
		rsa.keys.n = hexStringToBigInteger(publicKey);
		rsa.keys.e = global_e;
		BigInteger decrypted = c.modPow(rsa.keys.e, rsa.keys.n);
		return bigIntegerToHexString(decrypted);
	}

}
